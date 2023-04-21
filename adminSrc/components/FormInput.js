import { useEffect, useState } from 'react'
import { v4 as uuidv4 } from 'uuid'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import{ faTrashCan } from '@fortawesome/free-solid-svg-icons'

export default function FormInput(props) {
    const id = uuidv4()
    const [hasFile, setHasFile] = useState(false)

    useEffect(() => {
        if (props.type === "file" && props.value !== "") {
            setHasFile(true)
        }
    }, [])

    if (props.type !== "file") {
        return (
            <div className="mb-3">
                <label htmlFor={id} className="block">
                    <span className="capitalize mr-2">{props.name}</span>
                    <span className="text-warning">{props.warning}</span>
                </label>
                <input type={props.type} name={props.name} id={id} onChange={props.handleChange} className="border border-gray-800 w-[100%]" value={props.value} />
            </div>
        )
    } else if (!hasFile){
        return (
            <div className="mb-3">
                <label htmlFor={id} className="block">
                    <span className="capitalize mr-2">{props.name}</span>
                    <span className="text-warning">{props.warning}</span>
                </label>
                <input type={props.type} name={props.name} id={id} onChange={props.handleChange} className="w-[100%]" />
            </div>
        )
    } else {
        return (
            <div className="mb-3">
                <p className="capitalize mr-2">{props.name}</p>
                <div>
                    <button onClick={() => {setHasFile(false)}}><FontAwesomeIcon icon={faTrashCan} className='w-[15px] mr-5'/></button>
                    <span>{props.value.split("/").slice(-1)}</span>
                </div>
            </div>
        )
    }

}