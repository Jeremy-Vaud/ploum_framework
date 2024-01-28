import { FontAwesomeIcon } from '@fortawesome/react-fontawesome'
import { faCircleXmark } from '@fortawesome/free-regular-svg-icons'

export default function Modal(props) {

    return (
        <div className={props.visibility ? "absolute" : "hidden"}>
            <div className="modal">
                <div class="close-modal">
                    <FontAwesomeIcon onClick={props.hide} icon={faCircleXmark} />
                </div>
                {props.children}
            </div>
            <div onClick={props.hide} className="bg-modal"></div>
        </div>
    )
}