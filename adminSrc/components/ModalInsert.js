import { useState, useEffect } from "react"
import { v4 as uuidv4 } from 'uuid'
import Modal from "./Modal"
import FormCheckbox from "./FormCheckbox"
import FormInput from "./FormInput"
import FormTextarea from "./FormTextarea"
import FormImage from "./FormImage"
import FormSelect from "./FormSelect"
import FormSelectMulti from "./FormSelectMulti"
import Loading from "./Loading"
import FormDateTime from "./FormDateTime"
import FormRichText from "./FormRichText"




export default function ModalInsert(props) {
    const [visibility, setVisibility] = useState(false)
    const formId = useState(uuidv4())
    const [inputs, setInputs] = useState([])
    const [loading, setLoading] = useState("hidden")

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
                <form id={formId}>
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
                    })
                    }
                </form>
                <div className="text-center">
                    <button onClick={submit} className="btn-add mr-5">Ajouter</button>
                    <button onClick={hide} className="btn-cancel">Annuler</button>
                </div>
            </Modal>
            <Loading loading={loading} />
        </>
    )
}