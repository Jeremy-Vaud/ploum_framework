import { useEffect, useState } from "react"
import Table from "../components/Table"

export default function PageTable(props) {
    const [columns, setColumns] = useState([])
    const [form, setForm] = useState([])
    const [formUpdate, setFormUpdate] = useState([])

    useEffect(() => {
        let array1 = [];
        let array2 = [];
        let array3 = [];
        Object.entries(props.dataTable.fields).forEach(([name, obj]) => {
            obj.table.forEach((e) => {
                if (e === "columns") {
                    array1.push({ name: name })
                } else if (e === "insert") {
                    array2.push({ name: name, type: obj.type })
                } else if (e === "update") {
                    array3.push({ name: name, type: obj.type })
                }
            });
            setColumns(array1)
            setForm(array2)
            setFormUpdate(array3)
        });
    }, [])




    return (
        <>
            <h1 className="text-2xl text-center mb-6">{props.dataTable.title}</h1>
            <Table table={props.dataTable.className} columns={columns} form={form} formUpdate={formUpdate} logOut={props.logOut} />
        </>
    )
}