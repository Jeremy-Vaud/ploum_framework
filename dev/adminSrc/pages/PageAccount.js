import { useState } from "react"
import FormInput from "../components/FormInput"
import ModalPassword from "../components/ModalPassword"
import Loading from "../components/Loading"

export default function PageAccount(props) {
    const [inputs, setInputs] = useState([
        { name: "nom", value: props.session.nom, warning: "" },
        { name: "prenom", value: props.session.prenom, warning: "" },
        { name: "email", value: props.session.email, warning: "" }
    ])
    const [warning, setWarning] = useState("")
    const [success, setSuccess] = useState(false)
    const [loading, setLoading] = useState(false)

    function handleChange(evt) {
        let array = [];
        inputs.map(e => {
            if (e.name === evt.target.name) {
                array.push({ name: e.name, value: evt.target.value, warning: "" })
            } else {
                array.push(e)
            }
        })
        setInputs(array)
        setWarning("")
    }

    function setWarnings(data) {
        let array = [];
        inputs.map((e) => {
            if (data[e.name]) {
                array.push({ name: e.name, value: e.value, warning: data[e.name] })
            } else {
                array.push(e)
            }
        })
        setInputs(array)
    }

    function submit(e) {
        e.preventDefault()
        let form = document.getElementById("formAccount")
        let formData = new FormData(form)
        formData.append("action", "updateUser")
        formData.append("method", "user")
        setLoading(true)
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
                    props.setSession(result.session)
                    setSuccess(true)
                    setWarning("Modifications enregistrées")
                } else if (result.status === "invalid") {
                    setWarnings(result.data)
                } else if (result.status === "error") {
                    setSuccess(false)
                    setWarning(result.msg)
                }
            })
            .catch((e) => {
                console.log(e);
            })
    }

    return (
        <>
            <h1>Mon compte</h1>
            <form onSubmit={submit} className="max-w-[300px] mx-auto mb-5" id="formAccount">
                <input type="hidden" name="id" value={props.session.id} />
                {inputs.map(e => {
                    return (
                        <FormInput key={e.name} name={e.name} type="text" warning={e.warning} value={e.value} handleChange={handleChange} />
                    )
                })}
                <div className="text-center">
                    <p className={success ? "text-success h-8" : "text-warning h-8"}>{warning}</p>
                    <button type="submit" className="btn-add">Enregistrer</button>
                </div>
            </form>
            <ModalPassword />
            <Loading visibility={loading} />
        </>
    )
}