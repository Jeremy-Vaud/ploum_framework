import { NavLink } from 'react-router-dom'
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faHome, faBars, faRightFromBracket, faUser } from '@fortawesome/free-solid-svg-icons'
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
                <div className='nav-header'>
                    <button onClick={moveNav}><FontAwesomeIcon icon={faBars} size='2x' className={showNav ? 'nav-link-active' : 'nav-link-disable'} /></button>
                    <NavLink
                        to='/admin'
                        key='Nav-title'
                        end={true}
                        onClick={() => { setShowNav(false) }}
                        className="nav-title hidden xs:block">
                        Administration
                    </NavLink>
                    <button onClick={props.sendLogOut}><FontAwesomeIcon icon={faRightFromBracket} size='2x' className='nav-link-disable' /></button>
                </div>
                <aside className={showNav ? "nav-aside left-0"
                    : "nav-aside -left-full"}>
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
                        <NavLink
                            to='/admin/account'
                            key='MyAccount'
                            end={true}
                            onClick={moveNav}
                            className={({ isActive }) => {
                                return 'block no-underline ' + (isActive ? 'pannel-link-active' : 'pannel-link-disable')
                            }}>
                            <FontAwesomeIcon icon={faUser} className="mr-3" />
                            Mon compte
                        </NavLink>
                        {props.navigation.map((e) => {
                            if (e.className !== "App\\User" || props.session.role === "superAdmin") {
                                return (
                                    <NavLink
                                        to={'/admin/' + e.slug}
                                        key={e.slug}
                                        end={true}
                                        onClick={moveNav}
                                        className={({ isActive }) => {
                                            return 'block no-underline ' + (isActive ? 'pannel-link-active' : 'pannel-link-disable')
                                        }}>
                                        <FontAwesomeIcon icon={icons[e.icon]} className="mr-3" />
                                        {e.title}
                                    </NavLink>
                                )
                            }
                        })}
                    </nav>
                    <div onClick={moveNav} className={showNav ? 'nav-bg' : 'hidden'}></div>
                </aside>
                <div className='px-header_padding pt-header_height'>
                    {props.children}
                </div>
            </>
        )
    } else {
        return (
            <>
                <div className='nav-header'>
                    <NavLink
                        to='/admin'
                        key='Nav-title'
                        end={true}
                        onClick={() => { setShowNav(false) }}
                        className="nav-title mx-auto">
                        Administration
                    </NavLink>
                </div>
                <div className='px-header_padding pt-header_height'>
                    {props.children}
                </div>
            </>
        )
    }
}

