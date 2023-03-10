import { useState, useEffect } from "react"
import { v4 as uuidv4 } from 'uuid'

export default function FormSelectMulti(props) {
    const id = uuidv4()
    const [selected,setSelected] = useState([])

    useEffect(() => {
        let array = []
        props.dataSelect.map((e) => {
            let key = uuidv4()
            let selected = false
            for(let i = 0; i < props.value.length; i++) {
                if(props.value[i].id === e.value) {
                    selected = true
                    break
                }
            }
            array.push({...e,selected: selected, key:key})
        })
        setSelected(array)
    },[])

    function select(evt) {
        let int = parseInt(evt.target.getAttribute("value"))
        let array = []
        selected.map((e) => {
            if(e.value === int) {
                let selected = !e.selected
                array.push({...e,selected:selected})
            }else {
                array.push(e)
            }
        })
        setSelected(array)
    }

    return (
        <div className="mb-3">
            <label htmlFor={id} className="block">
                <span className="capitalize mr-2">{props.name}</span>
                <span className="text-warning">{props.warning}</span>
            </label>
            <input name={props.table} id={id} type="hidden" value={selected.map((e)=>{
                if(e.selected) {
                    return(
                       e.value 
                    )
                }
            })}></input>
                {selected.map((e) => {
                    return (
                        <span value={e.value} key={e.key} onClick={select} className={e.selected ? 
                            "select-multi-switch-active"
                        : "select-multi-switch-disable"}
                        >{e.name}</span>
                    )
                })}
        </div>
    )
}