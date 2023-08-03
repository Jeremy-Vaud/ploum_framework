import { useState } from "react"
import FormInput from "../components/FormInput"
import Loading from "../components/Loading"

export default function PageLogin(props) {
    const [email, setEmail] = useState("")
    const [password, setPassword] = useState("")
    const [warning, setWarning] = useState("")
    const [forgotPass, setForgotPass] = useState(false)
    const [loading, setLoading] = useState("hidden")

    function handleChange(e) {
        if (e.target.name === "email") {
            setEmail(e.target.value)
        } else if (e.target.name === "password") {
            setPassword(e.target.value)
        }
    }

    function submit(e) {
        e.preventDefault();
        let formData = new FormData
        let isLog = false
        formData.append("email", email)
        if (forgotPass) {
            formData.append("action", "forgotPass")
        } else {         
            formData.append("action", "logIn")
            formData.append("password", password)
        }
        setLoading("")
        fetch("/api", {
            method: 'POST',
            body: formData
        })
            .then((response) => {
                setLoading("hidden")
                if (response.status === 200) {
                    if (formData.get("action") === "logIn") {
                        isLog = true
                    } else {
                        setForgotPass(false)
                    }
                }
                return response.json()
            })
            .then((result) => {
                if(isLog) {
                    props.logIn(result)
                } else {
                    setWarning(result.warning)
                }
            })
    }

    function changeForm() {
        setWarning("")
        forgotPass ? setForgotPass(false) : setForgotPass(true)
    }

    return (
        <>
            <h1 className="text-2xl text-center mb-6">{forgotPass ? "Récupération de mot passe" : "login"}</h1>
            <div className="flex justify-center">
                <form className="min-w-[300px]" onSubmit={submit}>
                    <p className="text-warning h-8">{warning}</p>
                    <FormInput key="email" name="email" type="email" warning={null} value={email} handleChange={handleChange} />
                    {forgotPass ? "" : <FormInput key="password" name="password" type="password" warning="" value={password} handleChange={handleChange} />}
                    <div className="text-center">
                        {forgotPass ?
                            <>
                                <button type="submit" className="btn-add mb-2">Envoi d'email de récupération</button>
                                <div>
                                    <button type="button" onClick={changeForm} className="btn-link">Annuler</button>
                                </div>
                            </>
                            : <>
                                <div>
                                    <button type="button" onClick={changeForm} className="mb-2 btn-link">Mot de passe oublié</button>
                                </div>
                                <button type="submit" className="btn-add">Se connecter</button>
                            </>
                        }
                    </div>
                </form>
            </div>
            <Loading loading={loading} />
        </>
    )

}