import { Link } from "react-router-dom"
import { v4 as uuidv4 } from 'uuid'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import icons from '../icons'


export default function PageHome(props) {

    return (<>
        <h1 className="text-2xl text-center mb-6">Administration</h1>
        
        <div className="flex flex-wrap justify-betewen ">
            {props.navigation.map(e => {
                return (
                    <Link to={'/admin/'+e.title} key={uuidv4()} className="w-64 h-32 text-center rounded shadow-xl border border-gray-800 mx-auto mb-10 inline-block bg-gray-800 hover:bg-yellow-600 transition-color duration-1000 text-gray-300 hover:text-gray-800 text-xl uppercase p-6">
                        <FontAwesomeIcon icon={icons[e.icon]} />                  
                        <p>{e.title}</p>
                    </Link>
                )
            })}
        </div>
    </>
    )
}