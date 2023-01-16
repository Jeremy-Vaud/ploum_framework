import Table from "../components/Table";

export default function PageAcceuil(props) {
    const columns = [
        { name: "rang" },
        {name: "projet", key: "titre"}
    ]

    const form = [
        { name: "rang", type:"number"},     
        {name: "projet", type:"select", table:"Model\\Projet",key: "titre"}
    ]

    const formUpdate = [
        { name: "rang", type:"number"},
        {name: "projet", type:"select", table:"Model\\Projet",key: "titre"} 
    ]

    return (
        <>
            <h1 className="text-2xl text-center mb-6">Accueil</h1>
            <Table table="Model\Accueil" columns={columns} form={form} formUpdate={formUpdate} logOut={props.logOut} mode="detail"/>
        </>
    )
}