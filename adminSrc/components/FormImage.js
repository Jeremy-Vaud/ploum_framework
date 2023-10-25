import { useEffect, useState } from 'react'
import { v4 as uuidv4 } from 'uuid'

export default function FormImage(props) {
    const id = uuidv4()
    const [hasFile, setHasFile] = useState(false)
    const [hasTmp, setHasTmp] = useState(false)
    const [src, setSrc] = useState("");
    const [tmpSrc, setTmpSrc] = useState("");

    useEffect(() => {
        if (props.value !== null && props.value !== "") {
            setSrc("../" + props.value)
            setHasFile(true)
        }
    }, [])

    function change() {
        setHasFile(false)
    }

    function handleChange(e) {
        props.handleChange(e)
        setHasTmp(true)
        setTmpSrc(URL.createObjectURL(e.target.files[0]))
    }

    function tmpChange() {
        setHasTmp(false)
        document.getElementById(id).value = ""
    }

    return (
        <div className="mb-3">

            <label htmlFor={id} className="block">
                <span className="capitalize mr-2">{props.name}</span>
                <span className="text-warning">{props.warning}</span>
            </label>
            {hasFile ?
                <>
                    <img src={src} className="mx-auto" />
                    <div className="text-center">
                        <span onClick={change} className="btn-update mt-2">Supprimer l'image</span>
                    </div>
                </>
                :
                <>
                    <img src={tmpSrc} className={hasTmp ? "mx-auto" : "hidden"} />
                    <div className={hasTmp ? "text-center" : "hidden"}>
                        <span onClick={tmpChange} className="btn-update mt-2">Supprimer l'image</span>
                    </div>
                    <input type="file" name={props.name} id={id} onChange={handleChange} accept="image/*" className={hasTmp ? "hidden" : "file:btn-add"} />
                </>
            }
        </div>
    )
}