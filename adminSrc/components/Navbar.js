import { NavLink } from 'react-router-dom'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import * as icons from '@fortawesome/free-solid-svg-icons'


export default function Navbar(props) {
    return (
        <div className='flex'>
            <aside className="bg-gray-800 w-[250px]  p-3">
                <h1 className='text-gray-300 text-2xl pb-3'>Administration</h1>
                <nav>
                    <NavLink
                        to='/admin'
                        key='Home'
                        end={true}
                        className={({ isActive }) => {
                            return 'block no-underline ' + (isActive ? 'text-yellow-600 mb-2' : 'text-gray-300 mb-2')
                        }}>
                            <FontAwesomeIcon icon={icons["faHome"]} className="mr-3"/>
                        Accueil
                    </NavLink>
                    {props.navigation.map(e =>
                        <NavLink
                            to={'/admin/'+e.title}
                            key={e.title}
                            end={true}
                            className={({ isActive }) => {
                                return 'block no-underline ' + (isActive ? 'text-yellow-600 mb-2' : 'text-gray-300 mb-2')
                            }}>
                                <FontAwesomeIcon icon={icons[e.icon]} className="mr-3"/>
                            {e.title}
                        </NavLink>
                    )}
                </nav>
                <p className='mt-6 cursor-pointer text-gray-300 mb-2' onClick={props.sendLogOut}>Se déconnecter</p>
            </aside>
            <div className='p-3 h-screen w-full'>
                {props.children}
            </div>
        </div>
    )
}

