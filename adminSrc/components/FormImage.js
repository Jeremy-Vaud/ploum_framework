import { useEffect, useState } from 'react'
import { v4 as uuidv4 } from 'uuid'
import { urlSite } from '../settings'

export default function FormImage(props) {
    const id = uuidv4()
    const [hasFile, setHasFile] = useState(false)
    const [src, setSrc] = useState("");

    useEffect(() => {
        if (props.value !== "") {
            setSrc(urlSite + props.value)
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
                <span className="text-red-500">{props.warning}</span>
            </label>
            {hasFile ?
                <>
                    <img src={src} />
                    <div className="text-center">
                        <span onClick={change} className="inline-block py-2 px-4 mt-2 bg-yellow-600 hover:bg-yellow-500 cursor-pointer rounded">Modifier l'image</span>
                    </div>
                </>
                :
                <input type="file" name={props.name} id={id} onChange={props.handleChange} accept="image/*" className="w-[100%] file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-yellow-600  hover:file:bg-yellow-500 file:cursor-pointer" />}
        </div>
    )
}