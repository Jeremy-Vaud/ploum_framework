import { useState } from "react"
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import{ faTrashCan } from '@fortawesome/free-solid-svg-icons'
import Modal from "./Modal"
import Loading from "./Loading"


export default function ModalDelete(props) {
    const [visibility, setVisibility] = useState(false)
    const [loading, setLoading] = useState("hidden")

    function show() {
        setVisibility(true)
    }

    function hide() {
        setVisibility(false)
    }

    function deleteById() {
        let formData = new FormData
        formData.append("table", props.table)
        formData.append("id", props.id)
        formData.append("action", "delete")
        setLoading("")
        fetch("/api", {
            method: 'POST',
            body: formData
        })
            .then((response) => {
                setLoading("hidden")
                if (response.status === 404) {
                    throw new Error('not found')
                } else if (response.status === 401) {
                    props.logOut()
                    throw new Error('Connection requise')
                } else if (!response.ok) {
                    throw new Error('response not ok')
                } else {
                    props.deleteRow(props.id)
                }
            })
            .catch((e) => {
                console.log(e.message)
            })
    }

    return (
        <>
            <button onClick={show}><FontAwesomeIcon icon={faTrashCan} className='w-[15px]'/></button>
            <Modal visibility={visibility} hide={hide}>
                <div className="text-center">               
                    <p className="mb-3">Attention toutes suppression est définitive</p>
                    <button onClick={deleteById} className="btn-delete mr-5">Suprimer</button>
                    <button onClick={hide} className="btn-cancel">annuler</button>
                </div>
            </Modal>
            <Loading loading={loading}/>
        </>
    )
}