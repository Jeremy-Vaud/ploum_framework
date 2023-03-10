import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import{ faCaretDown } from '@fortawesome/free-solid-svg-icons'

export default function TableHead(props) {

    function setArrowClasses(sortStateVal) {
        if (sortStateVal === "sort") {
            return "w-[10px] inline"
        } else if (sortStateVal === "reverse") {
            return "w-[10px] inline rotate-180"
        } else {
            return "hidden"
        }
    }
    return (
        <thead>
            <tr className="bg-gray-800 text-white">
            <th className="w-[75px]">action</th>
                {props.columns.map(e =>
                    <th key={e.name} onClick={() => props.sort(e.name)}>
                        <div className="flex justify-between pr-5 cursor-pointer items-center">
                            <span>{e.name}</span>
                            <FontAwesomeIcon icon={faCaretDown} className={setArrowClasses(props.sortState[e.name])} />
                        </div>
                    </th>
                )}
            </tr>
        </thead>
    )
}