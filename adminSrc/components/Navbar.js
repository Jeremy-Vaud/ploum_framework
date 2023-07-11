import { NavLink } from 'react-router-dom'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faHome, faBars, faRightFromBracket } from '@fortawesome/free-solid-svg-icons'
import icons from '../icons'
import { useState } from 'react'


export default function Navbar(props) {
    const [showNav, setShowNav] = useState(false)

    function moveNav() {
        setShowNav(!showNav)
    }

    if (props.isConnect) {
        return (
            <>
                <div className='nav-bg px-3 py-1 fixed top-0 left-0 w-full z-10 flex justify-between items-center'>
                    <button onClick={moveNav}><FontAwesomeIcon icon={faBars} size='2x' className={showNav ? 'nav-link-active' : 'nav-link-disable'} /></button>
                    <NavLink
                        to='/admin'
                        key='Nav-title'
                        end={true}
                        onClick={() => { setShowNav(false) }}
                        className="nav-title">
                        Administration
                    </NavLink>
                    <button onClick={props.sendLogOut}><FontAwesomeIcon icon={faRightFromBracket} size='2x' className='nav-link-disable' /></button>
                </div>
                <aside className={showNav ? "nav-bg w-[250px] p-3 fixed top-0 left-0 h-screen transition-all z-0 pt-16"
                    : "nav-bg w-[250px] p-3 fixed top-0 -left-full h-screen transition-all z-0"}>
                    <nav>
                        <NavLink
                            to='/admin'
                            key='Home'
                            end={true}
                            onClick={moveNav}
                            className={({ isActive }) => {
                                return 'block no-underline ' + (isActive ? 'pannel-link-active' : 'pannel-link-disable')
                            }}>
                            <FontAwesomeIcon icon={faHome} className="mr-3" />
                            Accueil
                        </NavLink>
                        {props.navigation.map(e =>
                            <NavLink
                                to={'/admin/' + e.title}
                                key={e.title}
                                end={true}
                                onClick={moveNav}
                                className={({ isActive }) => {
                                    return 'block no-underline ' + (isActive ? 'pannel-link-active' : 'pannel-link-disable')
                                }}>
                                <FontAwesomeIcon icon={icons[e.icon]} className="mr-3" />
                                {e.title}
                            </NavLink>
                        )}
                    </nav>
                </aside>
                <div className='px-3 pt-16'>
                    {props.children}
                </div>
            </>
        )
    } else {
        return (
            <>
                <div className='nav-bg px-3 py-1 fixed top-0 left-0 w-full z-10 text-center'>
                    <NavLink
                        to='/admin'
                        key='Nav-title'
                        end={true}
                        onClick={() => { setShowNav(false) }}
                        className="nav-title">
                        Administration
                    </NavLink>
                </div>
                <div className='px-3 pt-16'>
                    {props.children}
                </div>
            </>
        )
    }
}

