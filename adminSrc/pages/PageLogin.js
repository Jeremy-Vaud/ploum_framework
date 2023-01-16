import { useState } from "react"
import FormInput from "../components/FormInput";

export default function PageLogin(props) {
    const [email, setEmail] = useState("")
    const [password, setPassword] = useState("")
    const [warning, setWarning] = useState("");

    function handleChange(e) {
        if(e.target.name === "email") {
            setEmail(e.target.value)
        }else if (e.target.name === "password") {
            setPassword(e.target.value)
        }
    }

    function submit() {
        let form = document.getElementById("logInForm")
        let data = {email:email,password:password}
        fetch("../api.php", {
            headers: {
                "Content-Type" : "application/json",
            }, method: 'POST', body: JSON.stringify(data)
        })
            .then((response) => {
                if(response.status === 401) {
                    return response.json();
                } else if(response.status === 200) {
                    props.logIn()
                    return response.json();
                }                         
            })
            .then((response) => {
                setWarning(response.warning)
            })
        }

    return (
        <>
            <h1 className="text-2xl text-center mb-6">login</h1>
            <div className="flex justify-center">
                <div className="min-w-[300px]">
                    <p className="h-8 text-red-600">{warning}</p>
                    <form id="logInForm" >
                        <FormInput key="email" name="email" type="email" warning={null} value={email} handleChange={handleChange} />
                        <FormInput key="password" name="password" type="password" warning="" value={password} handleChange={handleChange} />
                    </form>
                    <div className="text-center">
                        <button onClick={submit} className="px-5 py-2 bg-blue-600 hover:bg-blue-500 rounded text-white">Se connecter</button>
                    </div>
                </div>
            </div>
        </>
    )
}