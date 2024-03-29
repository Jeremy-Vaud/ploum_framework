import { useEffect, useState } from "react"
import Loading from "../components/Loading"
import { Link } from "react-router-dom"

export default function PageRecovery() {
    const code = new URLSearchParams(window.location.search).get('code')
    const [isValidLink, setIsValidLink] = useState(false)
    const [msg, setMsg] = useState("")
    const [loading, setLoading] = useState(false)

    useEffect(() => {
        if (code) {
            const formData = new FormData
            formData.append("method", "session")
            formData.append("action", "isValidRecoveryLink")
            formData.append("code", code)
            setLoading(true)
            fetch("/api", {
                method: "POST",
                body: formData
            })
                .then((response) => {
                    setLoading(false)
                    if (response.status === 200) {
                        return response.json()
                    } else {
                        return { isValid: false, msg: "Une erreur est survenue" }
                    }

                })
                .then((response) => {
                    setMsg(response.msg)
                    if (response.isValid) {
                        setIsValidLink(true)
                    }
                })
        } else {
            setMsg("Url non valide")
        }
    }, [])

    function submit(e) {
        e.preventDefault()
        const formData = new FormData(changePassForm)
        formData.set("action", "changePass")
        formData.set("method", "session")
        if (checkForm(formData)) {
            setLoading(true)
            fetch("/api", {
                method: 'POST',
                body: formData
            }).then((response) => {
                setLoading(false)
                if (response.status === 200) {
                    return response.json()
                } else {
                    return { isValid: true, msg: "Une erreur est survenue" }
                }
            })
                .then((result) => {
                    setIsValidLink(result.isValid)
                    setMsg(result.msg)
                })
        }
    }

    function checkForm(formData) {
        try {
            if (formData.get("pass1") === "") {
                throw new Error("Veuillez remplir les champs");

            }
            if (formData.get("pass1") !== formData.get("pass2")) {
                throw new Error("Les champs mot de passe ne sont pas identiques");

            }
            return true
        } catch (error) {
            setMsg(error.message)
            return false
        }
    }

    return (
        <>
            <h1>Modification de mot de passe</h1>
            <div className="max-w-[300px] mx-auto text-center">
                <p className="text-warning h-8">{msg}</p>
                {isValidLink ?
                    <form onSubmit={submit} id="changePassForm">
                        <input type="hidden" value={code} name="code" />
                        <div className="text-left mb-3">
                            <label for="pass1">Mot de passe</label>
                            <input type="password" name="pass1" id="pass1" />
                        </div>
                        <div className="text-left mb-3">
                            <label for="pass1">Mot de passe</label>
                            <input type="password" name="pass2" id="pass2" />
                        </div>
                        <button type="submit" className="btn-add">Modifier le mot de passe</button>
                    </form>
                    :
                    <Link to="/admin" className="btn-add">Se connecter</Link>
                }
            </div>
            <Loading visibility={loading} />
        </>
    )
}