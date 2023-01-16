import { useState } from "react"

export default function FormCheckbox(props) {
    const [checked, setChecked] = useState(props.value)

    function change(e) {
        setChecked(!checked)
        if(e.target.checked) {
            e.target.value = '1'
        } else {
            e.target.value = '0'
        }
        props.handleChange(e)
    }
        return (
            <div className="mb-3">
                <input type="checkbox" name={props.name} className="mr-2" onChange={change} checked={checked}/>
                <label htmlFor={props.name} className="capitalize">{props.name}</label>
            </div>
        )
}