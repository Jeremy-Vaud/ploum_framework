import { useState, useEffect } from "react"
import { v4 as uuidv4 } from 'uuid'
import TableHead from "./TableHead"
import TableRow from "./TableRow"
import ModalInsert from "./ModalInsert"
import TableSearch from "./TableSearch"
import { urlApi } from "../settings"

export default function TableToDetail(props) {
    const [data, setData] = useState([])
    const [sortState, setSortState] = useState([])
    const [hiddenRows, setHiddenRows] = useState({})


    function initSortState() {
        let array = []
        props.columns.map(e => array[e.name] = "default")
        setSortState(array)
    }

    function sort(e) {
        setData(
            [...data].sort((a, b) => {
                if (a[e] < b[e]) {
                    if (sortState[e] === "sort") {
                        return 1
                    } else {
                        return -1
                    }
                }
                if (a[e] > b[e]) {
                    if (sortState[e] === "sort") {
                        return -1
                    } else {
                        return 1
                    }
                }
                return 0;
            })
        )
        let array = []
        props.columns.map(row => {
            if (row.name === e) {
                if (sortState[e] === "sort") {
                    array[e] = "reverse"
                } else {
                    array[e] = "sort"
                }
            } else {
                array[row.name] = "default"
            }
        })
        setSortState(array)
    }

    function insert(line) {
        line.id = parseInt(line.id)
        setData([...data, line])
    }

    function deleteRow(id) {
        let array = []
        data.map((e) => {
            if (e.id !== id) {
                array.push(e)
            }
        })
        setData(array)
    }

    function updateRow(response) {
        let array = []
        data.map((e) => {
            if (e.id !== response.id) {
                array.push(e)
            } else {
                array.push(response)
            }
        })
        setData(array)
    }

    function search(e) {
        let json = {}
        let val = e.target.value
        data.map((row) => {
            let find = false
            props.columns.map((col) => {
                if (row[col.name].indexOf(val) !== -1) {
                    find = true;
                }
            })
            if (find) {
                json[row.id] = false
            }else {
                json[row.id] = true
            }
        })
        setHiddenRows(json)
    }

    useEffect(() => {
        fetch( urlApi+'?table=' + props.table + '&id=all')
            .then((response) => {
                if (response.status === 404) {
                    throw new Error('not found')
                } else if(response.status === 401) {
                    props.logOut()
                    throw new Error('Connection requise')
                }else if (!response.ok) {
                    throw new Error('response not ok')
                }
                return response.json()
            })
            .then((result) => {
                setData(result)
                initSortState()
            })
            .catch((e) => {
                console.log(e.message)
            })
    }, [])

    return (
        <>
            <div className="flex justify-between items-center mb-4">
                <ModalInsert form={props.form} table={props.table} insert={insert} logOut={props.logOut}/>
                <TableSearch search={search} />
            </div>
            <table className="w-full">
                <TableHead sort={sort} columns={props.columns} sortState={sortState} deleteRow={deleteRow} />
                <tbody>
                    {
                    data ? data.map(e => <TableRow key={uuidv4()} table={props.table} data={e} columns={props.columns} deleteRow={deleteRow} formUpdate={props.formUpdate} updateRow={updateRow} hidden={hiddenRows[e.id]} logOut={props.logOut}/>) : null}
                </tbody>
            </table>
        </>
    )
}