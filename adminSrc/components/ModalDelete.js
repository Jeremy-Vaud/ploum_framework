import { useState } from "react"
import trash from "../icons/trash-can-solid.svg"
import Loading from "./Loading"
import { urlApi } from "../settings"

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
        fetch(urlApi, {
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
            <button onClick={show}><img src={trash} className='w-[15px} h-[15px]' /></button>
            <div className={visiblity}>
                <div className="fixed top-[50%] left-[50%] translate-x-[-50%] translate-y-[-50%] text-center p-10 z-20 bg-white">
                    <p className="mb-3">Attention toutes suppression est définitive</p>
                    <button onClick={deleteById} className="px-5 py-2 bg-yellow-600 hover:bg-yellow-500 rounded mr-5">Suprimer</button>
                    <button onClick={hide} className="px-5 py-2 bg-gray-300 hover:bg-gray-200 rounded">annuler</button>
                </div>
                <div onClick={hide} className="fixed top-0 left-0 w-screen h-screen opacity-40 bg-black"></div>
            </div>
            <Loading loading={loading}/>
        </>
    )
}