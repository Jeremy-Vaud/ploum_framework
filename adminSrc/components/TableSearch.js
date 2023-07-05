import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import{ faMagnifyingGlass } from '@fortawesome/free-solid-svg-icons'

export default function TableSearch(props) {
    return (
        <div className="flex items-center">
            <FontAwesomeIcon icon={faMagnifyingGlass} className="h-[20px] mr-2"/>
            <input type="text" onChange={props.search}/>
        </div>
    )
}