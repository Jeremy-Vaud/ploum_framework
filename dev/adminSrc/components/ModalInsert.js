import { useState, useEffect } from "react"
import { v4 as uuidv4 } from 'uuid'
import Modal from "./Modal"
import Loading from "./Loading"
import Form from "./Form"

export default function ModalInsert(props) {
    const [visibility, setVisibility] = useState(false)
    const formId = useState(uuidv4())
    const [inputs, setInputs] = useState([])
    const [loading, setLoading] = useState(false)

    function show() {
        setVisibility(true)
    }

    function hide() {
        setVisibility(false)
    }

    function reset() {
        let array = [];
        props.form.map((e) => {
            array.push({ key: uuidv4(), name: e.name, type: e.type, warning: "", value: "" })
        })
        setInputs(array)
    }

    useEffect(() => {
        reset()
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
        formData.append("table", props.table)
        formData.append("action", "insert")
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
                    hide()
                    reset()
                    props.insert(result.data)
                } else if (result.status === "invalid") {
                    setWarnings(result.data)
                }
            })
            .catch((e) => {
                console.log(e);
            })
    }

    return (
        <>
            <button onClick={show} className="btn-add">Ajouter</button>
            <Modal visibility={visibility} hide={hide}>
                <Form formId={formId} table={props.table} id={null} inputs={inputs} dataSelect={props.dataSelect} handleChange={handleChange} logOut={props.logOut} />
                <div className="text-center">
                    <button onClick={submit} className="btn-add mr-5">Ajouter</button>
                    <button onClick={hide} className="btn-cancel">Annuler</button>
                </div>
            </Modal>
            <Loading visibility={loading} />
        </>
    )
}