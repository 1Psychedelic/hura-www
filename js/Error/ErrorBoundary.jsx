import React from 'react'
import {Exceptionless} from "@exceptionless/browser";

class ErrorBoundary extends React.Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false };
    }

    static getDerivedStateFromError(error) {
        return { hasError: true };
    }

    componentDidCatch(error, errorInfo) {
        Exceptionless.submitException(error);
    }

    render() {
        if (this.state.hasError) {
            return (
                <div className="alert alert-danger">
                    <p>Ajaj, na stránce se stala nějaká chyba. Co teď?</p>
                    <ul>
                        <li><p>Zkuste obnovit stránku (na počítači pomocí <strong>F5</strong> nebo <strong>Ctrl+F5</strong>).</p></li>
                        <li><p>Zkuste se odhlásit a znovu přihlásit - odhlášením dojde k vyčištění paměti prohlížeče.</p></li>
                        <li><p>Zkuste se vrátit později - snad chybu mezitím opravíme.</p></li>
                    </ul>
                    <p>Omlouváme se za tuto nepříjemnost.</p>
                </div>
            );
        }

        return this.props.children;
    }
}

export default ErrorBoundary;
