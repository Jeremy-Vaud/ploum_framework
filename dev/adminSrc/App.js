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
import { lazy, Suspense } from 'react'
const LazyCloud = lazy(() => import("./pages/PageCloud"))

export function App() {
    const [loading, setLoading] = useState(true)
    const [isConnect, setIsConnect] = useState(false)
    const [session, setSession] = useState(null)
    const navigation = [...data.pages].sort((a, b) => {
        return a.order - b.order
    })
    const cloud = true

    function logIn(session) {
        setIsConnect(true)
        setSession(session)
    }

    function logOut() {
        setIsConnect(false)
        setSession(null)
    }

    function isLog() {
        const formData = new FormData
        formData.append("method", "session")
        formData.append("action", "isLog")
        fetch("/api", {
            method: "POST",
            body: formData
        })
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
        const formData = new FormData
        formData.append("method", "session")
        formData.append("action", "logOut")
        setLoading(true)
        fetch("/api", {
            method: "POST",
            body: formData
        })
            .then((response) => {
                if (response.status === 200) {
                    setIsConnect(false);
                }
                setLoading(false)
            })
    }

    if (!loading) {
        return (
            <BrowserRouter>
                <Navbar sendLogOut={sendLogOut} navigation={navigation} isConnect={isConnect} session={session} cloud={data.cloud}>
                    <Routes>
                        <Route path='/admin' key={uuidv4()} element={isConnect ? <PageHome logOut={logOut} navigation={navigation} session={session} cloud={data.cloud} /> : <PageLogin logIn={logIn} />} />
                        <Route path='/admin/account' key={uuidv4()} element={isConnect ? <PageAccount logOut={logOut} session={session} setSession={setSession} /> : <PageLogin logIn={logIn} />} />
                        {session ? (navigation.map(e => {
                            if (e.className !== "App\\User" || session.role === "superAdmin") {
                                if (e.type === "table") {
                                    return (
                                        <Route path={'/admin/' + e.slug} key={uuidv4()} element={isConnect ? <PageTable logOut={logOut} dataTable={e} key={uuidv4()} setSession={setSession} /> : <PageLogin logIn={logIn} />} />
                                    )
                                } else if (e.type === "edit_area") {
                                    return (
                                        <Route path={'/admin/' + e.slug} key={uuidv4()} element={isConnect ? <PageEditArea logOut={logOut} dataTable={e} key={uuidv4()} setSession={setSession} /> : <PageLogin logIn={logIn} />} />
                                    )
                                }
                            }
                        })) : null}
                        {data.cloud ?
                            <Route path='/admin/cloud' key={uuidv4()} element={isConnect ? <PageCloud logOut={logOut} key={uuidv4()} setSession={setSession} /> : <PageLogin logIn={logIn} />} />
                            : null}
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

const PageCloud = () => (
    <Suspense fallback={
        <div className="spin-page-container">
            <svg className="spin-page" viewBox="0 0 100 101" fill="none">
                <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor" />
                <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" />
            </svg>
        </div>
    }>
        <LazyCloud />
    </Suspense>
);