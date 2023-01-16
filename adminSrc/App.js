import { BrowserRouter, Routes, Route } from 'react-router-dom'
import { useState, useEffect } from 'react'


import Navbar from './components/Navbar'
import PageLogin from './pages/PageLogin'
import NotFound from './pages/NotFound'
import PageHome from './pages/PageHome'
import PageTable from './pages/pageTable'

import data from './data.json'
//import PageUsers from './pages/PageUsers'

// Pages spécifique à ploum studio
//import PageProjects from './pages/PageProjects'
//import PageTags from './pages/PageTags'
//import PageAcceuil from './pages/PageAccueil'

export function App() {
    const [isConnect, setIsConnect] = useState(false)
    const navigation = [...data].sort((a, b) => {
        return a.order - b.order
    })

    function logIn() {
        setIsConnect(true)
    }

    function logOut() {
        setIsConnect(false)
    }

    useEffect(() => {
        fetch("../api.php" + '?isLog=1')
            .then((response) => {
                if (response.status === 200) {
                    setIsConnect(true)
                }
            })
            .catch((e) => {
                console.log(e.message)
            })
    }, [])

    function sendLogOut() {
        fetch("../api.php" + '?logOut=1')
            .then((response) => {
                if (response.status === 200) {
                    setIsConnect(false);
                }
            })
            .catch((e) => {
                console.log(e.message)
            })
    }

    return (
        <BrowserRouter>
            <Navbar sendLogOut={sendLogOut} navigation={navigation}>
                <Routes>
                    <Route path='/admin' element={isConnect ? <PageHome logOut={logOut} navigation={navigation}/> : <PageLogin logIn={logIn} />} />
                    {navigation.map(e => {
                        return (
                            <Route path={'/admin/'+e.title} element={isConnect ? <PageTable logOut={logOut} /> : <PageLogin logIn={logIn} />} />
                        )
                    })}
                    <Route path='*' element={isConnect ? <NotFound /> : <PageLogin logIn={logIn} />} />
                </Routes>
            </Navbar>
        </BrowserRouter>
    )
}