import { v4 as uuidv4 } from 'uuid';
import ModalDelete from './ModalDelete';
import ModalUpdate from './ModalUpdate';

export default function TableRow(props) {
    if (!props.hidden) {
        return (
            <tr>
                <td className="w-24">
                    <ModalUpdate table={props.table} data={props.data} formUpdate={props.formUpdate} updateRow={props.updateRow} logOut={props.logOut} dataSelect={props.dataSelect} setSession={props.setSession} />
                    <ModalDelete table={props.table} id={props.data.id} deleteRow={props.deleteRow} logOut={props.logOut} />
                </td>
                {props.columns.map((column) => {
                    let name = props.data[column.name]
                    if (props.dataSelect[column.name]) {
                        for(let i = 0; i < props.dataSelect[column.name].length; i++) {
                            if(props.data[column.name] === props.dataSelect[column.name][i].value) {
                                name = props.dataSelect[column.name][i].name
                                break
                            }
                        }
                    }
                    return (
                        <td key={uuidv4()}>{name}</td>
                    )
                })}
            </tr>
        )
    }
}