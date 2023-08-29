import FormRichText from "../components/FormRichText";
import { useState } from 'react';

export default function PageTest() {
    const [value, setValue] = useState({
        "ops": [
          {
            "insert": "Gandalf the Grey\n"
          }
        ]
      })

    function handleChange(e) {
        console.log(e)
    }
    return (
        <FormRichText value={value} handleChange={handleChange} warning="warning" name="test"/>
    )
}