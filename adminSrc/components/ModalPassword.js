import { useState } from "react"
import Modal from "./Modal"
import Loading from "./Loading"

export default function ModalPassword() {
    const [visibility, setVisibility] = useState(false)
    const [loading, setLoading] = useState(false)
    const [isUpdate, setIsUpdate] = useState(false)
    const [warning, setWarning] = useState("")

    function hide() {
        setVisibility(false)
    }

    function show() {
        setVisibility(true)
        setIsUpdate(false)
        setWarning("")
        document.getElementById("pass").value = ""
        document.getElementById("newPass1").value = ""
        document.getElementById("newPass2").value = ""
    }

    function submit(e) {
        e.preventDefault()
        let form = document.getElementById("formPassword")
        let formData = new FormData(form)
        formData.append("action", "updatePass")
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
                } else if(response.status !== 200) {
                    throw new Error("Status : " + response.status)
                }
                return response.json()
            })
            .then((result) => {
                if ((result.status === "success")) {
                    setIsUpdate(true)
                } else if (result.status === "invalid") {
                    setWarning(result.warning)
                }
            })
            .catch((e) => {
                setWarning("Une erreur est survenue")
                console.log(e)
            })
    }

    return (
        <>
            <div className="text-center">
                <button className="btn-link" onClick={show}>Changer de mot de passe</button>
            </div>
            {isUpdate ?
                <Modal visibility={visibility} hide={hide}>
                    <div className="text-center">
                        <p className="mb-3">Mot de passe mis à jour</p>
                        <button onClick={hide} className="btn-cancel">Fermer</button>
                    </div>
                </Modal>
                :
                <Modal visibility={visibility} hide={hide}>
                    <form id="formPassword" onSubmit={submit}>
                        <label htmlFor="pass">Mot de passe</label>
                        <input className="mb-3" type="password" name="pass" id="pass" />
                        <label htmlFor="newPass1">Nouveau Mot de passe</label>
                        <input className="mb-3" type="password" name="newPass1" id="newPass1" />
                        <label htmlFor="newPass2">Nouveau Mot de passe</label>
                        <input className="mb-3" type="password" name="newPass2" id="newPass2" />
                        <div className="text-center">
                            <p className="text-warning h-8">{warning}</p>
                            <button onClick={submit} className="btn-update mr-5">Modifier</button>
                            <button onClick={hide} className="btn-cancel">Annuler</button>
                        </div>
                    </form>
                </Modal>
            }
            <Loading visibility={loading} />
        </>
    )
}