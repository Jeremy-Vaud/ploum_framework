import FormCheckbox from "./FormCheckbox"
import FormInput from "./FormInput"
import FormTextarea from "./FormTextarea"
import FormImage from "./FormImage"
import FormSelect from "./FormSelect"
import FormSelectMulti from "./FormSelectMulti"
import FormDateTime from "./FormDateTime"
import FormRichText from "./FormRichText"
import FormFile from "./FormFile"

export default function Form(props) {

    return (
        <form id={props.formId}>
            {props.inputs.map(e => {
                if (e.type === "checkbox") {
                    return (
                        <FormCheckbox key={e.key} name={e.name} value={e.value} handleChange={props.handleChange} />
                    )
                } else if (e.type === "textarea") {
                    return (
                        <FormTextarea key={e.key} name={e.name} warning={e.warning} value={e.value} handleChange={props.handleChange} />
                    )
                } else if (e.type === "image") {
                    return (
                        <FormImage key={e.key} name={e.name} warning={e.warning} value={e.value} handleChange={props.handleChange} />
                    )
                } else if (e.type === "select" && props.dataSelect[e.name]) {
                    return (
                        <FormSelect key={e.key} name={e.name} warning={e.warning} value={e.value} handleChange={props.handleChange} dataSelect={props.dataSelect[e.name]} />
                    )
                } else if (e.type === "selectMulti" && props.dataSelect[e.name]) {
                    return (
                        <FormSelectMulti key={e.key} name={e.name} warning={e.warning} value={e.value} dataSelect={props.dataSelect[e.name]} handleChange={props.handleChange} />
                    )
                } else if (e.type === "dateTime") {
                    return (
                        <FormDateTime key={e.key} name={e.name} warning={e.warning} value={e.value} handleChange={props.handleChange} />
                    )
                } else if (e.type === "richText") {
                    return (
                        <FormRichText key={e.key} name={e.name} warning={e.warning} value={e.value} handleChange={props.handleChange} />
                    )
                } else if (e.type === "file") {
                    return (
                        <FormFile key={e.key} name={e.name} warning={e.warning} value={e.value} handleChange={props.handleChange} table={props.table} editArea={props.editArea} id={props.id} logOut={props.logOut} />
                    )
                } else {
                    return (
                        <FormInput key={e.key} name={e.name} type={e.type} warning={e.warning} value={e.value} handleChange={props.handleChange} />
                    )
                }
            })
            }
        </form>
    )
}