import { v4 as uuidv4 } from 'uuid'

export default function FormSelect(props) {
    const id = uuidv4()
    return (
        <div className="mb-3">
            <label htmlFor={id} className="block">
                <span className="capitalize mr-2">{props.name}</span>
                <span className="text-red-500">{props.warning}</span>
            </label>
            <select name={props.name} id={id} onChange={props.handleChange} className="border border-gray-800 w-[100%]" value={props.value}>
                {props.dataSelect.map((e) => {
                    return (
                        <option value={e.value} key={uuidv4()}>{e.name}</option>
                    )
                })}
            </select>
        </div>
    )
}