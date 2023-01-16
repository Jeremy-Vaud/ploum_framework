import magnifying from '../icons/magnifying-glass-solid.svg'

export default function TableSearch(props) {
    return (
        <div className="flex">
            <img src={magnifying} className="w-[20px] mr-2"/>
            <input type="text" className="border border-gray-800" onChange={props.search}/>
        </div>
    )
}