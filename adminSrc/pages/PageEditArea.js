import { useEffect, useState } from "react"
import { v4 as uuidv4 } from 'uuid'
import FormCheckbox from "../components/FormCheckbox"
import FormInput from "../components/FormInput"
import FormTextarea from "../components/FormTextarea"
import FormImage from "../components/FormImage"
import FormSelect from "../components/FormSelect"
import FormSelectMulti from "../components/FormSelectMulti"
import Loading from "../components/Loading"
import FormDateTime from "../components/FormDateTime"
import FormRichText from "../components/FormRichText"

export default function PageEditArea(props) {
    const formId = useState(uuidv4())
    const [inputs, setInputs] = useState([])
    const [loading, setLoading] = useState("hidden")
    const [modalVisiblity, setModalVisibility] = useState("hidden")

    useEffect(() => {
        setLoading("")
        fetch("/api" + '?edit_area=' + props.dataTable.className)
            .then((response) => {
                setLoading("hidden")
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
                {
                    Object.entries(props.dataTable.fields).forEach(([name, obj]) => {
                        array.push({ key: uuidv4(), name: name, type: obj.type, warning: "", value: result[name] })
                    })
                }
                setInputs(array)
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
        setLoading("")
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
                setLoading("hidden")
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
        setModalVisibility("")
    }

    function hide() {
        setModalVisibility("hidden")
    }

    return (
        <>
            <h1 className="text-2xl text-center mb-6">{props.dataTable.title}</h1>
            <form id={formId} className="max-w-lg mx-auto">
                {inputs.map(e => {
                    if (e.type === "checkbox") {
                        return (
                            <FormCheckbox key={e.key} name={e.name} value={e.value} handleChange={handleChange} />
                        )
                    } else if (e.type === "textarea") {
                        return (
                            <FormTextarea key={e.key} name={e.name} type={e.type} warning={e.warning} value={e.value} handleChange={handleChange} />
                        )
                    } else if (e.type === "image") {
                        return (
                            <FormImage key={e.key} name={e.name} type={e.type} warning={e.warning} value={e.value} handleChange={handleChange} />
                        )
                    } else if (e.type === "select" && props.dataSelect[e.name]) {
                        return (
                            <FormSelect key={e.key} name={e.name} type={e.type} warning={e.warning} value={e.value} handleChange={handleChange} dataSelect={props.dataSelect[e.name]} />
                        )
                    } else if (e.type === "selectMulti" && props.dataSelect[e.name]) {
                        let table;
                        for (let i = 0; i < props.form.length; i++) {
                            if (props.form[i].name === e.name) {
                                table = props.form[i].table
                                break
                            }
                        }
                        let value = []
                        return (
                            <FormSelectMulti key={e.key} name={e.name} type={e.type} warning={e.warning} value={value} dataSelect={props.dataSelect[e.name]} table={table} />
                        )
                    } else if (e.type === "dateTime") {
                        return (
                            <FormDateTime key={e.key} name={e.name} type={e.type} warning={e.warning} value={e.value} handleChange={handleChange} />
                        )
                    } else if (e.type === "richText") {
                        return (
                            <FormRichText key={e.key} name={e.name} type={e.type} warning={e.warning} value={e.value} handleChange={handleChange} />
                        )
                    } else {
                        return (
                            <FormInput key={e.key} name={e.name} type={e.type} warning={e.warning} value={e.value} handleChange={handleChange} />
                        )
                    }
                })}
            </form>
            <div className="text-center">
                <button onClick={submit} className="btn-add mt-5">Enregistrer</button>
            </div>
            <div className={modalVisiblity}>
                <div className="fixed top-[50%] left-[50%] translate-x-[-50%] translate-y-[-50%] text-center p-10 z-40 bg-white">
                    <p className="mb-3">Enregistrement réussi</p>
                    <button onClick={hide} className="btn-cancel">Fermer</button>
                </div>
                <div onClick={hide} className="fixed top-0 left-0 w-screen h-screen opacity-40 z-30 bg-black"></div>
            </div>
            <Loading loading={loading} />
        </>
    )
}