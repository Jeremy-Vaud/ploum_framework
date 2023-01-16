import { v4 as uuidv4 } from 'uuid'

export default function FormInput(props) {
    const id = uuidv4()

    return (
        <div className="mb-3">
            <label htmlFor={id} className="block">
                <span className="capitalize mr-2">{props.name}</span>
                <span className="text-red-500">{props.warning}</span>
            </label>
            <input type={props.type} name={props.name} id={id} onChange={props.handleChange} className="border border-gray-800 w-[100%]" value={props.value}/>
        </div>
    )
}