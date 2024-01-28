import { useState, useEffect } from "react"
import { v4 as uuidv4 } from 'uuid'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faPen } from '@fortawesome/free-solid-svg-icons'
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

export default function ModalUpdate(props) {
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

    useEffect(() => {
        let array = [];
        props.formUpdate.map((e) => {
            array.push({ key: uuidv4(), name: e.name, type: e.type, warning: "", value: props.data[e.name] })
        })
        setInputs(array)
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
        formData.append("action", "update")
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
                    props.updateRow(result.data)
                    if (result.session) {
                        props.setSession(result.session)
                    }
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
            <button onClick={show}><FontAwesomeIcon icon={faPen} className='w-[15px] mr-5' /></button>
            <Modal visibility={visibility} hide={hide}>
                <form id={formId}>
                    <input type="hidden" name="id" value={props.data.id} />
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
                            let value
                            if (typeof e.value === "string") {
                                value = e.value
                            } else {
                                value = e.value.id
                            }
                            return (
                                <FormSelect key={e.key} name={e.name} type={e.type} warning={e.warning} value={value} handleChange={handleChange} dataSelect={props.dataSelect[e.name]} />
                            )
                        } else if (e.type === "selectMulti" && props.dataSelect[e.name]) {
                            let table;
                            for (let i = 0; i < props.formUpdate.length; i++) {
                                if (props.formUpdate[i].name === e.name) {
                                    table = props.formUpdate[i].table
                                    break
                                }
                            }
                            return (
                                <FormSelectMulti key={e.key} name={e.name} type={e.type} warning={e.warning} value={e.value} dataSelect={props.dataSelect[e.name]} table={table} />
                            )
                        } else if (e.type === "dateTime") {
                            return (
                                <FormDateTime key={e.key} name={e.name} type={e.type} warning={e.warning} value={e.value} id={e.id} handleChange={handleChange} />
                            )
                        } else if (e.type === "richText") {
                            return (
                                <FormRichText key={e.key} name={e.name} type={e.type} warning={e.warning} value={e.value} handleChange={handleChange} />
                            )
                        } else {
                            return (
                                <FormInput key={e.key} name={e.name} type={e.type} warning={e.warning} value={e.value} id={e.id} handleChange={handleChange} />
                            )
                        }
                    })
                    }
                </form>
                <div className="text-center">
                    <button onClick={submit} className="btn-update mr-5">Enregistrer les modifications</button>
                    <button onClick={hide} className="btn-cancel">Annuler</button>
                </div>
            </Modal>
            <Loading visibility={loading} />
        </>
    )
}