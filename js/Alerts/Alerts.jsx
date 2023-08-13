import React from 'react'


class Alerts extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            'alerts': {}
        }

        this.onCloseClick = this.onCloseClick.bind(this);
        this.addAlert = this.addAlert.bind(this);
        this.dismissAlert = this.dismissAlert.bind(this);
        this.dismissAllAlerts = this.dismissAllAlerts.bind(this);
    }

    dismissAlert(key) {
        let alert = this.state.alerts[key] || {'state': 'vanishing'};

        if (alert.state === 'vanishing') {
            return;
        }

        let alertsState = JSON.parse(JSON.stringify(this.state.alerts));
        alertsState[alert.key].state = 'vanishing';
        alertsState[alert.key].animationTimeout = setTimeout(() => {
            let alertsState = JSON.parse(JSON.stringify(this.state.alerts));
            delete alertsState[alert.key];
            this.setState({
                'alerts': alertsState
            });
        }, 200);

        this.setState({
            'alerts': alertsState
        });
    }

    dismissAllAlerts() {
        for (let i in this.state.alerts) {
            this.dismissAlert(i);
        }
    }

    addAlert(key, type, message) {
        let alertState = 'spawning';
        if (this.state.alerts[key]) {
            if (this.state.alerts[key].state !== 'idle') {
                clearTimeout(this.state.alerts[key].animationTimeout);
            }
            alertState = 'idle';
        }

        let alert = {
            'key': key,
            'type': type,
            'message': message,
            'state': alertState,
            'animationTimeout': setTimeout(() => {
                let alertsState = JSON.parse(JSON.stringify(this.state.alerts));
                alertsState[key].state = 'grabbing-attention';
                alertsState[key].animationTimeout = setTimeout(() => {
                    let alertsState = JSON.parse(JSON.stringify(this.state.alerts));
                    alertsState[key].state = 'idle';
                    this.setState({
                        'alerts': alertsState
                    });
                }, 200);
                this.setState({
                    'alerts': alertsState
                });
            }, 20),
        };

        let alertsState = JSON.parse(JSON.stringify(this.state.alerts));
        alertsState[key] = alert;
        this.setState({
            'alerts': alertsState
        });
    }

    onCloseClick(alert) {
        this.dismissAlert(alert.key);
    }

    render() {
        let createAlert = (alert) => {
            return (
                <div key={alert.key} className={"alert alert-" + alert.type + " " + alert.state} role="alert">
                    {alert.message}
                    <button className="close" aria-label="Zavřít" onClick={() => {this.onCloseClick(alert);}}>
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
            );
        };

        let createAlerts = (alerts) => {
            return Object.values(alerts).map(createAlert);
        };

        return (
            <div className="alerts">
                {createAlerts(this.state.alerts)}
            </div>
        )
    }
}

export default Alerts
