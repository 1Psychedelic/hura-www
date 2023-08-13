import React, {useEffect} from 'react'
import {
    useHistory
} from "react-router-dom";

const AlertsAutoDismiss = (props) => {

    const history = useHistory();

    useEffect(() => {
        return history.listen((location) => {
            props.dismissAllAlerts();
        })
    },[history])

    return (<></>)
};

export default AlertsAutoDismiss
