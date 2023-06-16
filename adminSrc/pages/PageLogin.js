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

    function submit() {
        let data = { email: email}
        if(forgotPass) {
            data.action = 'forgotPass'
        } else {
            data.action = "logIn"
            data.password = password
        }
        setLoading("")
        fetch("../api.php", {
            headers: {
                "Content-Type": "application/json",
            }, method: 'POST', body: JSON.stringify(data)
        })
            .then((response) => {
                setLoading("hidden")
                if (response.status === 401) {
                    return response.json();
                } else if (response.status === 200) {
                    props.logIn()
                    return response.json()
                }
            })
            .then((response) => {
                setWarning(response.warning)
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
                <div className="min-w-[300px]">
                    <p className="text-warning h-8">{warning}</p>
                    <form>
                        <FormInput key="email" name="email" type="email" warning={null} value={email} handleChange={handleChange} />
                        {forgotPass ? "" : <FormInput key="password" name="password" type="password" warning="" value={password} handleChange={handleChange} />}
                    </form>
                    <div className="text-center">
                        {forgotPass ?
                            <>
                                <button onClick={submit} className="btn-add mb-2">Envoi d'email de récupération</button>
                                <div>
                                    <button onClick={changeForm} className="btn-link">Annuler</button>
                                </div>
                            </>
                            : <>
                                <div>
                                    <button onClick={changeForm} className="mb-2 btn-link">Mot de passe oublié</button>
                                </div>
                                <button onClick={submit} className="btn-add">Se connecter</button>
                            </>
                        }
                    </div>
                </div>
            </div>
            <Loading loading={loading}/>
        </>
    )

}