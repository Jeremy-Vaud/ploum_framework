import { useEffect, useState } from 'react'
import Loading from '../components/Loading'
import Modal from '../components/Modal'
import { setChonkyDefaults } from 'chonky'
import { ChonkyIconFA } from 'chonky-icon-fontawesome'
import {
    FileBrowser,
    FileNavbar,
    FileToolbar,
    FileList,
    FileContextMenu,
    ChonkyIconName,
    ChonkyActions,
    defineFileAction,
    FileHelper
} from "chonky";
setChonkyDefaults({ iconComponent: ChonkyIconFA })

export default function PageCloud(props) {
    const [files, setFiles] = useState([])
    const [folderChain, setFolderChain] = useState([{ id: 'folder-0', name: 'cloud' }])
    const [newFolderModal, setNewFolderModal] = useState(false)
    const [renameModal, setRenameModal] = useState(false)
    const [loading, setLoading] = useState(false)

    const frenchI18n = {
        locale: 'fr',
        formatters: {
            formatFileModDate: (intl, file) => {
                const safeModDate = FileHelper.getModDate(file);
                if (safeModDate) {
                    return `${intl.formatDate(safeModDate)}, ${intl.formatTime(
                        safeModDate
                    )}`;
                } else {
                    return null;
                }
            }
        },
        messages: {
            'chonky.toolbar.searchPlaceholder': 'Recherche',
            'chonky.toolbar.selectedFileCount': `{fileCount, plural,
                =0 {}
                one {# sélectionné}
                other {# sélectionnés}
            }`,
            'chonky.fileList.nothingToShow': 'Dossier vide',
            'chonky.contextMenu.browserMenuShortcut': 'Menu: {shortcut}',
            [`chonky.actionGroups.Actions`]: 'Actions',
            [`chonky.actionGroups.Options`]: 'Trier',
            [`chonky.actions.${ChonkyActions.CreateFolder.id}.button.name`]: 'Nouveau dossier',
            [`chonky.actions.${ChonkyActions.CreateFolder.id}.button.tooltip`]: 'Nouveau dossier',
            [`chonky.actions.${ChonkyActions.DeleteFiles.id}.button.name`]: 'Supprimer',
            [`chonky.actions.${ChonkyActions.OpenSelection.id}.button.name`]: 'Ouvrir sélection',
            [`chonky.actions.${ChonkyActions.SelectAllFiles.id}.button.name`]: 'Tout sélectionner',
            [`chonky.actions.${ChonkyActions.ClearSelection.id}.button.name`]: 'Tout désélectionner',
            [`chonky.actions.${ChonkyActions.EnableListView.id}.button.name`]: 'Liste',
            [`chonky.actions.${ChonkyActions.EnableGridView.id}.button.name`]: 'Grille',
            [`chonky.actions.${ChonkyActions.SortFilesByName.id}.button.name`]: 'Par nom',
            [`chonky.actions.${ChonkyActions.SortFilesBySize.id}.button.name`]: 'Par taille',
            [`chonky.actions.${ChonkyActions.SortFilesByDate.id}.button.name`]: 'Par date',
            [`chonky.actions.${ChonkyActions.ToggleHiddenFiles.id}.button.name`]: 'Fichiers cachés',
            [`chonky.actions.${ChonkyActions.ToggleShowFoldersFirst.id}.button.name`]: 'Dossiers en premier',
            [`chonky.actions.${ChonkyActions.UploadFiles.id}.button.name`]: 'Upload',
            [`chonky.actions.${ChonkyActions.UploadFiles.id}.button.tooltip`]: 'Upload',
            [`chonky.actions.${ChonkyActions.DownloadFiles.id}.button.name`]: 'Télécharger',
        },
    };

    const rename = defineFileAction({
        id: "rename",
        button: {
            name: "Renomer",
            contextMenu: true,
            icon: ChonkyIconName.placeholder
        }
    })

    const myFileActions = [
        ChonkyActions.CreateFolder,
        ChonkyActions.UploadFiles,
        ChonkyActions.DeleteFiles,
        ChonkyActions.MoveFiles,
        ChonkyActions.DownloadFiles,
        ChonkyActions.OpenFiles,
        ChonkyActions.SelectAllFiles,
        ChonkyActions.ClearSelection,
        ChonkyActions.EnableListView,
        ChonkyActions.EnableGridView,
        ChonkyActions.SortFilesByName,
        ChonkyActions.SortFilesByDate,
        ChonkyActions.SortFilesBySize,
        ChonkyActions.ToggleHiddenFiles,
        ChonkyActions.ToggleShowFoldersFirst,
        rename
    ];

    const handleAction = (data) => {
        if (data.id === ChonkyActions.OpenFiles.id) {
            openFiles(data)
        } else if (data.id === ChonkyActions.CreateFolder.id) {
            setNewFolderModal(true)
        } else if (data.id === ChonkyActions.DeleteFiles.id) {
            deleteFiles(data)
        } else if (data.id === ChonkyActions.UploadFiles.id) {
            document.getElementById("upload-files").click()
        } else if (data.id === ChonkyActions.MoveFiles.id) {
            moveFiles(data)
        } else if (data.id === ChonkyActions.DownloadFiles.id) {
            downloadFiles(data)
        } else if (data.id === rename.id) {
            renameFile(data)
        }
    };

    function openFiles(data) {
        if (data.payload.files[0].isDir === undefined) {
            let newFolderChain = []
            for (let i = 0; i < folderChain.length; i++) {
                newFolderChain.push(folderChain[i])
                if (folderChain[i].id === data.payload.files[0].id) {
                    break
                }
            }
            setFolderChain(newFolderChain)
        } else if (data.payload.files[0].isDir) {
            const id = "folder-" + (parseInt(folderChain[folderChain.length - 1].id.substring(7)) + 1)
            setFolderChain([...folderChain, { id: id, name: data.payload.files[0].name }])
        } else {
            downloadFiles(data)
        }
    }

    function getDir() {
        let data = new FormData
        data.append("action", "getDir")
        data.append("path", getPath())
        setLoading(true)
        fetch("/api", {
            method: 'POST',
            body: data
        })
            .then((response) => {
                if (response.status === 401) {
                    props.logOut()
                    throw new Error('Connection requise')
                } else if (response.status !== 200) {
                    throw new Error("Status : " + response.status)
                }
                return response.json()
            })
            .then((result) => {
                setLoading(false)
                setFiles(result)
            })
            .catch((e) => {
                console.log(e)
            })
    }

    function addNewFolder() {
        const inputName = document.getElementById("newFolderName")
        if (inputName.value !== "") {
            let form = new FormData
            form.append("action", "createFolder")
            form.append("name", inputName.value)
            sendData(form)
        }
        inputName.value = ""
        setNewFolderModal(false)
    }

    function deleteFiles(data) {
        let form = new FormData
        form.append("action", "deleteFiles")
        let files = []
        for (let i = 0; i < data.state.selectedFiles.length; i++) {
            files.push(data.state.selectedFiles[i].name)
        }
        form.append("files", files)
        sendData(form)
    }

    function uploadFiles(e) {
        let form = new FormData
        form.append("action", "uploadFiles")
        for (let i = 0; i < e.target.files.length; i++) {
            form.append(`files[${i}]`, e.target.files[i])
        }
        sendData(form)
    }

    function moveFiles(data) {
        let form = new FormData
        form.append("action", "moveFiles")
        form.append("destination", data.payload.destination.id)
        for (let i = 0; i < data.payload.files.length; i++) {
            form.append(`files[${i}]`, data.payload.files[i].id)
        }
        sendData(form)
    }

    function downloadFiles(data) {
        for (let i = 0; i < data.state.selectedFiles.length; i++) {
            const fileName = data.state.selectedFiles[i].isDir ? data.state.selectedFiles[i].name + ".zip" : data.state.selectedFiles[i].name
            let form = new FormData
            form.append("action", "downloadFile")
            form.append("file", data.state.selectedFiles[i].id)
            fetch("/api", {
                method: 'POST',
                body: form
            })
                .then((response) => {

                    if (response.status === 401) {
                        props.logOut()
                        throw new Error('Connection requise')
                    } else if (response.status !== 200) {
                        throw new Error("Status : " + response.status)
                    }
                    return response.blob()
                })
                .then((result) => {
                    let a = document.createElement("a");
                    a.href = window.URL.createObjectURL(result);
                    a.download = fileName;
                    a.click();
                })
                .catch((e) => {
                    console.log(e)
                })
        }
    }

    function renameFile(data) {
        if (data.state.contextMenuTriggerFile) {
            setRenameModal(true)
            document.getElementById("renameInput").value = data.state.contextMenuTriggerFile.name
            document.getElementById("oldName").value = data.state.contextMenuTriggerFile.name
        }
    }

    function sendRename() {
        const oldName = document.getElementById("oldName")
        const inputName = document.getElementById("renameInput")
        if (inputName.value !== "") {
            let form = new FormData
            form.append("action", "renameFile")
            form.append("oldName", oldName.value)
            form.append("newName", inputName.value)
            sendData(form)
        }
        setRenameModal(false)
    }

    useEffect(() => {
        getDir()
    }, [])

    useEffect(() => {
        getDir()
    }, [folderChain])

    function getPath() {
        let path = "";
        for (let i = 1; i < folderChain.length; i++) {
            path += `${folderChain[i].name}/`
        }
        return path
    }

    function sendData(form) {
        form.append("path", getPath())
        setLoading(true)
        fetch("/api", {
            method: 'POST',
            body: form
        })
            .then((response) => {
                if (response.status === 401) {
                    props.logOut()
                    throw new Error('Connection requise')
                } else if (response.status !== 200) {
                    throw new Error("Status : " + response.status)
                }
            })
            .then((res) => {
                getDir()
            })
            .catch((e) => {
                setLoading(false)
                console.log(e)
            })
    }

    return (
        <>
            <h1>Cloud</h1>
            <div className='cloud'>
                <FileBrowser
                    files={files}
                    folderChain={folderChain}
                    fileActions={myFileActions}
                    onFileAction={handleAction}
                    clearSelectionOnOutsideClick={true}
                    disableDefaultFileActions={true}
                    i18n={frenchI18n}
                >
                    <FileNavbar />
                    <FileToolbar />
                    <FileList />
                    <FileContextMenu />
                </FileBrowser>
            </div>
            <Modal visibility={newFolderModal} hide={() => { setNewFolderModal(false) }}>
                <label for="newFolderName">Nouveau dossier</label>
                <input type="text" id="newFolderName" className='mb-3' />
                <div className="text-center">
                    <button onClick={addNewFolder} className="btn-add mr-5">Créer</button>
                    <button onClick={() => { setNewFolderModal(false) }} className="btn-cancel">Annuler</button>
                </div>
            </Modal>
            <Modal visibility={renameModal} hide={() => { setRenameModal(false) }}>
                <input type="hidden" id="oldName" />
                <label for="renameInput">Nom</label>
                <input type="text" id="renameInput" className='mb-3' />
                <div className="text-center">
                    <button onClick={sendRename} className="btn-add mr-5">Renomer</button>
                    <button onClick={() => { setRenameModal(false) }} className="btn-cancel">Annuler</button>
                </div>
            </Modal>
            <input type="file" id="upload-files" name="files" onChange={uploadFiles} className="hidden" multiple />
            <Loading visibility={loading} />
        </>
    )
}