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
                    <Link to={'/admin/'+e.title} key={uuidv4()} className="home-card">
                        <FontAwesomeIcon icon={icons[e.icon]} />                  
                        <p>{e.title}</p>
                    </Link>
                )
            })}
        </div>
    </>
    )
}