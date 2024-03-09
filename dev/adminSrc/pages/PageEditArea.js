import { useEffect, useState } from "react"
import { v4 as uuidv4 } from 'uuid'
import Modal from "../components/Modal"
import Loading from "../components/Loading"
import Form from "../components/Form"

export default function PageEditArea(props) {
    const formId = useState(uuidv4())
    const [inputs, setInputs] = useState([])
    const [dataSelect, setDataSelect] = useState({})
    const [loading, setLoading] = useState(false)
    const [modalVisibility, setModalVisibility] = useState(false)

    useEffect(() => {
        const formData = new FormData
        formData.append("action", "getEditArea")
        formData.append("edit_area", props.dataTable.className)
        setLoading(true)
        fetch("/api", {
            method: 'POST',
            body: formData
        })
            .then((response) => {
                setLoading(false)
                if (response.status === 404) {
                    throw new Error('not found')
                } else if (response.status === 401) {
                    props.logOut()
                    throw new Error('Connection requise')
                } else if (!response.ok) {
                    throw new Error('response not ok')
                }
                return response.json()
            })
            .then((result) => {
                let array = [];
                Object.entries(props.dataTable.fields).forEach(([name, obj]) => {
                    array.push({ key: uuidv4(), name: name, type: obj.type, warning: "", value: result.data[name] })
                })
                setInputs(array)
                setDataSelect(result.dataSelect)
            })
            .catch((e) => {
                console.log(e.message)
            })
    }, [])

    function handleChange(evt) {
        let array = [];
        inputs.map((e) => {
            if (e.name === evt.target.name) {
                array.push({ key: e.key, name: e.name, type: e.type, warning: "", value: evt.target.value })
            } else {
                array.push(e)
            }
        })
        setInputs(array)
    }

    function setWarnings(data) {
        let array = [];
        inputs.map((e) => {
            if (data[e.name]) {
                array.push({ key: e.key, name: e.name, type: e.type, warning: data[e.name], value: e.value })
            } else {
                array.push(e)
            }
        })
        setInputs(array)
    }

    function submit() {
        let form = document.getElementById(formId)
        let checkbox = form.querySelectorAll('input[type=checkbox]')
        let formData = new FormData(form)
        formData.append("edit_area", props.dataTable.className)
        formData.append("action", "upsert")
        setLoading(true)
        checkbox.forEach((input) => {
            if (!input.checked) {
                formData.append(input.name, "0")
            }
        })
        fetch("/api", {
            method: 'POST',
            body: formData
        })
            .then((response) => {
                setLoading(false)
                if (response.status === 401) {
                    props.logOut()
                    throw new Error('Connection requise')
                }
                return response.json()
            })
            .then((result) => {
                if ((result.status === "success")) {
                    show()
                } else if (result.status === "invalid") {
                    setWarnings(result.data)
                }
            })
            .catch((e) => {
                console.log(e);
            })
    }

    function show() {
        setModalVisibility(true)
    }

    function hide() {
        setModalVisibility(false)
    }

    return (
        <>
            <h1>{props.dataTable.title}</h1>
            <Form formId={formId} editArea={props.dataTable.className} id={null} inputs={inputs} dataSelect={dataSelect} handleChange={handleChange} logOut={props.logOut} />
            <div className="text-center">
                <button onClick={submit} className="btn-add mb-5">Enregistrer</button>
            </div>
            <Modal visibility={modalVisibility} hide={hide} >
                <div className="text-center">
                    <p className="mb-3">Enregistrement réussi</p>
                    <button onClick={hide} className="btn-cancel">Fermer</button>
                </div>
            </Modal>
            <Loading visibility={loading} />
        </>
    )
}