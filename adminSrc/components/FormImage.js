import { useEffect, useState } from 'react'
import { v4 as uuidv4 } from 'uuid'

export default function FormImage(props) {
    const id = uuidv4()
    const [hasFile, setHasFile] = useState(false)
    const [src, setSrc] = useState("");

    useEffect(() => {
        if (props.value !== "") {
            setSrc("../"+props.value)
            setHasFile(true)
        }
    }, [])

    function change() {
        setHasFile(false)
    }

    return (
        <div className="mb-3">

            <label htmlFor={id} className="block">
                <span className="capitalize mr-2">{props.name}</span>
                <span className="text-warning">{props.warning}</span>
            </label>
            {hasFile ?
                <>
                    <img src={src} />
                    <div className="text-center">
                        <span onClick={change} className="btn-update mt-2">Supprimer l'image</span>
                    </div>
                </>
                :
                <input type="file" name={props.name} id={id} onChange={props.handleChange} accept="image/*" className="file:btn-add" />}
        </div>
    )
}