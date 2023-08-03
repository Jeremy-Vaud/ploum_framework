import { Link } from "react-router-dom"
import { v4 as uuidv4 } from 'uuid'
import { faUser } from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import icons from '../icons'


export default function PageHome(props) {

    return (<>
        <h1 className="text-2xl text-center mb-6">Administration</h1>

        <div className="flex flex-wrap justify-betewen ">
            <Link to={'/admin/account'} key={uuidv4()} className="home-card">
                <FontAwesomeIcon icon={faUser} />
                <p>Mon Compte</p>
            </Link>
            {props.session ? (props.navigation.map(e => {
                if (e.className !== "App\\User" || props.session.role === "superAdmin") {
                    return (
                        <Link to={'/admin/' + e.title} key={uuidv4()} className="home-card">
                            <FontAwesomeIcon icon={icons[e.icon]} />
                            <p>{e.title}</p>
                        </Link>
                    )
                }
            })) : null}
        </div>
    </>
    )
}