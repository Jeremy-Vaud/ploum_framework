import { useState, useEffect } from "react"
import { v4 as uuidv4 } from 'uuid'
import TableHead from "./TableHead"
import TableRow from "./TableRow"
import ModalInsert from "./ModalInsert"
import TableSearch from "./TableSearch"
import Loading from "./Loading"
import { urlApi } from "../settings"

export default function Table(props) {
    const [data, setData] = useState([])
    const [sortState, setSortState] = useState([])
    const [hiddenRows, setHiddenRows] = useState({})
    const [loading, setLoading] = useState("hidden")
    const [dataSelect, setDataSelect] = useState({})


    function initSortState() {
        let array = []
        props.columns.map(e => array[e.name] = "default")
        setSortState(array)
    }

    function sort(e) {
        setData(
            [...data].sort((a, b) => {
                let c = a[e]
                let d = b[e]
                if(typeof a[e] === "object") {
                    for (var i = 0; i < props.columns.length; i++) {
                        if(props.columns[i].name === e) {
                            c= a[e][props.columns[i].key]
                            d = b[e][props.columns[i].key]
                            break;
                        }
                    }
                }
                if (c < d) {
                    if (sortState[e] === "sort") {
                        return 1
                    } else {
                        return -1
                    }
                }
                if (c > d) {
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
                let name
                if(typeof row[col.name] === 'object') {
                    name = String(row[col.name][col.key])

                } else {
                    name = String(row[col.name])
                }
                if (name.indexOf(val) !== -1) {
                    find = true;
                }
            })
            if (find) {
                json[row.id] = false
            } else {
                json[row.id] = true
            }
        })
        setHiddenRows(json)
    }

    useEffect(() => {
        setLoading("")
        fetch(urlApi + '?table=' + props.table + '&id=all')
            .then((response) => {
                setLoading("hidden")
                if (response.status === 404) {
                    throw new Error('not found')
                } else if (response.status === 401) {
                    props.logOut()
                    throw new Error('Connection requise')
                } else if (!response.ok) {
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

    function loadSelect(table,name,key) {
        setLoading("")
        fetch(urlApi + '?table=' + table + '&id=all')
            .then((response) => {
                setLoading("hidden")
                if (response.status === 404) {
                    throw new Error('not found')
                } else if (response.status === 401) {
                    props.logOut()
                    throw new Error('Connection requise')
                } else if (!response.ok) {
                    throw new Error('response not ok')
                }
                return response.json()
            })
            .then((response) => {
                let copyDataSelect = dataSelect;
                let value = [];
                response.map((e) => {
                    value.push({value:e.id,name:e[key]})
                })
                copyDataSelect[name] = value
                setDataSelect(copyDataSelect)
     
            })
            .catch((e) => {
                console.log(e.message)
            })
    }

    useEffect(() => {
        props.form.map((e) => {
            if (e.type === "select" || e.type === "selectMulti") {
                loadSelect(e.table,e.name,e.key)
            }
        })
    }, [])

    return (
        <>
            <div className="flex justify-between items-center mb-4">
                <ModalInsert form={props.form} table={props.table} insert={insert} logOut={props.logOut} dataSelect={dataSelect}/>
                <TableSearch search={search} />
            </div>
            <table className="w-full">
                <TableHead sort={sort} columns={props.columns} sortState={sortState} deleteRow={deleteRow} />
                <tbody>
                    {
                        data ? data.map(e => <TableRow key={uuidv4()} table={props.table} data={e} columns={props.columns} deleteRow={deleteRow} formUpdate={props.formUpdate} updateRow={updateRow} hidden={hiddenRows[e.id]} logOut={props.logOut} dataSelect={dataSelect}/>) : null}
                </tbody>
            </table>
            <Loading loading={loading} />
        </>
    )
}