import React, { useRef } from 'react';
import { Editor } from '@tinymce/tinymce-react';
import { v4 as uuidv4 } from 'uuid'

export default function FormRichText(props) {
    const editorRef = useRef(null)
    const id = uuidv4()

    function onChange() {
        let evt = { target: { name: props.name, value: "" } }
        if (editorRef.current) {
            evt.target.value = editorRef.current.getContent()
            props.handleChange(evt)
        }
    }
    return (
        <>
            <input type="hidden" name={props.name} id={id} value={props.value} />
            <span className="block">
                <span className="capitalize mr-2">{props.name}</span>
                <span className="text-warning">{props.warning}</span>
            </span>
            <Editor
                tinymceScriptSrc="../../lib/tinymce/tinymce.min.js"
                onInit={(evt, editor) => editorRef.current = editor}
                initialValue={props.value}
                onChange={onChange}
                init={{
                    height: 500,
                    menubar: false,
                    statusbar: false,
                    plugins: ['link'],
                    toolbar: 'undo redo | bold italic underline | link',
                    language: "fr_FR"
                }}
            />
        </>
    );
}