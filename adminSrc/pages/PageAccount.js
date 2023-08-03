import { useEffect, useState } from "react"
import FormInput from "../components/FormInput"
import Loading from "../components/Loading"

export default function PageAccount(props) {
    const [inputs, setInputs] = useState([
        { name: "nom", value: props.session.nom, warning: "" },
        { name: "prenom", value: props.session.prenom, warning: "" },
        { name: "email", value: props.session.email, warning: "" }
    ])
    const [warning, setWarning] = useState("")
    const [loading, setLoading] = useState("hidden")

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
        setLoading("")
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
                    props.setSession(result.session)
                    setWarning("Modifications enregistrées")
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
            <h1 className="text-2xl text-center mb-6">Mon compte</h1>
            <form onSubmit={submit} className="max-w-[300px] mx-auto" id="formAccount">
                <input type="hidden" name="id" value={props.session.id} />
                {inputs.map(e => {
                    return (
                        <FormInput key={e.name} name={e.name} type="text" warning={e.warning} value={e.value} handleChange={handleChange} />
                    )
                })}
                <div className="text-center">
                    <p className="text-warning h-8">{warning}</p>
                    <button type="submit" className="btn-add">Enregistrer</button>
                </div>
            </form>
            <Loading loading={loading} />
        </>
    )
}