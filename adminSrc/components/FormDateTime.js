import { useEffect, useState } from 'react'
import { v4 as uuidv4 } from 'uuid'

export default function FormDateTime(props) {
    const id = uuidv4()
    const [date, setDate] = useState("")
    const [time, setTime] = useState("")

    useEffect(() => {
        if(props.value !== "") {
            const split = props.value.split(" ")
            setDate(split[0])
            setTime(split[1])
        }
    },[])

    function changeDate(e) {
        setDate(e.target.value)
        const event = {
            target: {
                value: e.target.value + " " + time,
                name: props.name
            }
        }
        props.handleChange(event)
    }

    function changeTime(e) {
        setTime(e.target.value)
        const event = {
            target: {
                value: date + " " + e.target.value,
                name: props.name
            }
        }
        props.handleChange(event)
    }

    return (
        <div className="mb-3">
            <label className="block">
                <span className="capitalize mr-2">{props.name}</span>
                <span className="text-warning">{props.warning}</span>
            </label>
            <input type="hidden" name={props.name} id={id} value={props.value} />
            <input type="date" value={date} className="border border-gray-800 w-[50%]" onChange={changeDate} />
            <input type="time" value={time} className="border border-gray-800 w-[50%]" onChange={changeTime} />
        </div>
    )
}