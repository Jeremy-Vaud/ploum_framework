import { useEffect, useState } from 'react'
import { v4 as uuidv4 } from 'uuid'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faTrashCan, faDownload } from '@fortawesome/free-solid-svg-icons'

export default function FormFile(props) {
    const id = uuidv4()
    const [hasFile, setHasFile] = useState(false)
    const [hasTmp, setHasTmp] = useState(false)

    useEffect(() => {
        if (props.value !== "" && props.value !== null) {
            setHasFile(true)
        }
    }, [])

    function handleChange(e) {
        props.handleChange(e)
        setHasTmp(true)
    }

    function deleteTmp() {
        setHasTmp(false)
        document.getElementById(id).value = ""
    }

    function download(e) {
        e.preventDefault()
        let formData = new FormData
        if (props.table) {
            formData.append("table", props.table)
            formData.append("action", "downloadTable")
        } else if (props.editArea) {
            formData.append("edit_area", props.editArea)
            formData.append("action", "downloadEditArea")
        }
        if (props.id) {
            formData.append("id", props.id)
        }
        formData.append("field", props.name)
        fetch("/api", {
            method: 'POST',
            body: formData
        })
            .then((response) => {
                if (response.status === 401) {
                    props.logOut()
                    throw new Error('Connection requise')
                }
                if (response.status !== 200) {
                    throw new Error('Une erreur est survenue')
                }
                return response.blob()
            })
            .then((result) => {
                let a = document.createElement("a");
                a.href = window.URL.createObjectURL(result);
                a.download = props.value.split("/").slice(-1);
                a.click();
            })
            .catch((e) => {
                console.log(e);
            })
    }

    return (
        <div className="mb-3">
            <label htmlFor={id} className="block">
                <span className="capitalize mr-2">{props.name}</span>
                <span className="text-warning">{props.warning}</span>
            </label>
            {!hasFile ?
                <>
                    <input type="file" name={props.name} id={id} onChange={handleChange} className={hasTmp ? "hidden file:btn-add" : "file:btn-add"} />
                    {hasTmp ?
                        <>
                            <button onClick={deleteTmp} className='mr-5'><FontAwesomeIcon icon={faTrashCan} className='w-[15px]' /></button>
                            <span>{props.value.split("\\").slice(-1)}</span>
                        </>
                        :
                        null
                    }
                </>
                :
                <>
                    <button onClick={() => { setHasFile(false) }} className='mr-5'><FontAwesomeIcon icon={faTrashCan} className='w-[15px]' /></button>
                    <span>{props.value.split("/").slice(-1)}</span>
                    <button onClick={download} className='ml-5'><FontAwesomeIcon icon={faDownload} className='w-[15px]' /></button>
                </>
            }
        </div>
    )
}