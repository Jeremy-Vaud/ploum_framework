import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import{ faMagnifyingGlass } from '@fortawesome/free-solid-svg-icons'

export default function TableSearch(props) {
    return (
        <div className="flex">
            <FontAwesomeIcon icon={faMagnifyingGlass} className="w-[20px]"/>
            <input type="text" className="border border-gray-800" onChange={props.search}/>
        </div>
    )
}