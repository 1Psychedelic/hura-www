import React from 'react'
import { useEffect } from "react";
import { useLocation } from "react-router-dom";

class ScrollManager extends React.Component {

    constructor(props) {
        super(props);
        this.scrollTarget = null;
    }

    setScrollTarget(ref) {
        console.log({'setScrollTarget': ref});
        this.scrollTarget = ref;
    }

    render() {
        return (
            <></>
        );
    }
}

export default ScrollManager;
