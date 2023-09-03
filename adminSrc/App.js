import { BrowserRouter, Routes, Route } from 'react-router-dom'
import { useState, useEffect } from 'react'
import { v4 as uuidv4 } from 'uuid'
import Navbar from './components/Navbar'
import PageLogin from './pages/PageLogin'
import NotFound from './pages/NotFound'
import PageHome from './pages/PageHome'
import PageTable from './pages/pageTable'
import PageEditArea from './pages/PageEditArea'
import PageAccount from './pages/PageAccount'
import PageRecovery from './pages/PageRecovery'
import data from './data.json'
import Loading from './components/Loading'

export function App() {
    const [loading, setLoading] = useState(true)
    const [isConnect, setIsConnect] = useState(false)
    const [session, setSession] = useState(null)
    const navigation = [...data].sort((a, b) => {
        return a.order - b.order
    })

    function logIn(session) {
        setIsConnect(true)
        setSession(session)
    }

    function logOut() {
        setIsConnect(false)
        setSession(null)
    }

    function isLog() {
        fetch("/api" + '?isLog=1')
            .then((response) => {
                setLoading(false)
                if (response.status === 200) {
                    return response.json()
                } else {
                    throw new Error("Non connecté")
                }
            })
            .then((result) => {
                logIn(result)
            })
            .catch((e) => {
                console.log(e.message)
            })
    }

    useEffect(isLog, [])

    function sendLogOut() {
        setLoading(true)
        fetch("/api" + '?logOut=1')
            .then((response) => {
                if (response.status === 200) {
                    setIsConnect(false);
                }
                setLoading(false)
            })
            .catch((e) => {
                console.log(e.message)
            })
    }

    if (!loading) {
        return (
            <BrowserRouter>
                <Navbar sendLogOut={sendLogOut} navigation={navigation} isConnect={isConnect} session={session}>
                    <Routes>
                        <Route path='/admin' key={uuidv4()} element={isConnect ? <PageHome logOut={logOut} navigation={navigation} session={session} /> : <PageLogin logIn={logIn} />} />
                        <Route path='/admin/account' key={uuidv4()} element={isConnect ? <PageAccount logOut={logOut} session={session} setSession={setSession} /> : <PageLogin logIn={logIn} />} />
                        {session ? (navigation.map(e => {
                            if (e.className !== "App\\User" || session.role === "superAdmin") {
                                if (e.type === "table") {
                                    return (
                                        <Route path={'/admin/' + e.title} key={uuidv4()} element={isConnect ? <PageTable logOut={logOut} dataTable={e} key={uuidv4()} setSession={setSession} /> : <PageLogin logIn={logIn} />} />
                                    )
                                } else if(e.type === "edit_area") {
                                    return (
                                        <Route path={'/admin/' + e.title} key={uuidv4()} element={isConnect ? <PageEditArea logOut={logOut} dataTable={e} key={uuidv4()} setSession={setSession} /> : <PageLogin logIn={logIn} />} />
                                    )
                                }
                            }
                        })) : null}
                        <Route path='/admin/recovery' element={<PageRecovery />} />
                        <Route path='*' element={isConnect ? <NotFound /> : <PageLogin logIn={logIn} />} />
                    </Routes>
                </Navbar>
            </BrowserRouter>
        )
    } else {
        return (
            <Loading loading="" />
        )
    }
}