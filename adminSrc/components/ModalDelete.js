import { useState } from "react"
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import{ faTrashCan } from '@fortawesome/free-solid-svg-icons'
import Loading from "./Loading"

export default function ModalDelete(props) {
    const [visiblity, setVisibility] = useState("hidden")
    const [loading, setLoading] = useState("hidden")

    function show() {
        setVisibility("")
    }

    function hide() {
        setVisibility("hidden")
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
            <div className={visiblity}>
                <div className="fixed top-[50%] left-[50%] translate-x-[-50%] translate-y-[-50%] text-center p-10 z-20 bg-white">
                    <p className="mb-3">Attention toutes suppression est définitive</p>
                    <button onClick={deleteById} className="btn-delete mr-5">Suprimer</button>
                    <button onClick={hide} className="btn-cancel">annuler</button>
                </div>
                <div onClick={hide} className="fixed top-0 left-0 w-screen h-screen opacity-40 bg-black"></div>
            </div>
            <Loading loading={loading}/>
        </>
    )
}