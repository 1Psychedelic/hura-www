import React from 'react'
import duration from 'dayjs/plugin/duration';
import 'dayjs/locale/cs'
import dayjs from 'dayjs';
import {
    BrowserRouter as Router,
    Switch,
    Route,
    Redirect,
    Link,
    useParams,
    useLocation,
    useRouteMatch, withRouter
} from "react-router-dom";
import {Helmet} from "react-helmet";
import api from "../Api/Api";
import FacebookConnect from "../Authentication/FacebookConnect";
import FacebookLogin from "../Authentication/FacebookLogin";
import GoogleConnect from "../Authentication/GoogleConnect";

dayjs.locale('cs');
dayjs.extend(duration);

const UserSettings = (props) => {

    if (!props.authentication.isLoggedIn) {
        return (
            <>
                <section className="page__section page__section--generic-heading">
                    <div className="page__section__content">
                        <span className="page__section__subheading">&nbsp;</span>
                        <h2 className="page__section__heading">Nastavení účtu</h2>
                    </div>
                </section>
                <section className="page__section page__section--user-profile">
                    <div className="page__section__content">

                        <h3>Tato stránka vyžaduje přihlášení</h3>

                    </div>
                </section>
            </>
        );
    }

    let relativeTime = (when) => {
        let diff = dayjs().diff(dayjs(when), 'milliseconds');
        let duration = dayjs.duration(diff);

        if (duration.years() > 0) {
            return 'před ' + duration.years() + ' lety';
        }
        if (duration.months() > 0) {
            return 'před ' + duration.months() + ' měsíci';
        }
        if (duration.days() > 0) {
            return 'před ' + duration.days() + ' dny';
        }
        if (duration.hours() > 0) {
            return 'před ' + duration.hours() + ' hodinami';
        }
        if (duration.minutes() > 0) {
            if (duration.minutes() > 15) {
                return 'před ' + duration.minutes() + ' minutami';
            }

            return 'právě teď';
        }

        return 'právě teď';
    };

    let disableUserSession = (id) => {

        api.request('POST', 'api/v1/authentication/disable-session', {'sessionId': id}, (response, data) => {
            if (response.status === 200) {
                props.addAlert('userSettings', 'success', 'Zařízení bude do 15 minut odhlášené z vašeho účtu.');
                props.onUserSessionsChanged(data.userSessions);
            } else {
                props.addAlert('userSettings', 'danger', data.message || 'Zařízení bude do 15 minut odhlášené z vašeho účtu.');
            }
        });
    }

    let createLogoutButton = (userSession) => {
        if (userSession.isCurrent) {
            return (
                <>
                    Aktuální zařízení
                </>
            );
        }

        return (
            <a key={userSession.id} onClick={() => {disableUserSession(userSession.id)}} className="login-button login-button-logout">Odhlásit toto zařízení</a>
        );
    };

    let createUserSession = (userSession) => {
        return (
            <div key={userSession.id} className="user-profile-session">
                <div className="user-profile-session-heading">
                    <div>
                        <h4><strong>{userSession.deviceDescription}</strong></h4> · {userSession.ip}
                    </div>
                    <div>
                        {createLogoutButton(userSession)}
                    </div>
                </div>
                <div>
                    <div>
                        <strong>Datum přihlášení:</strong> {dayjs(userSession.createdAt).format('Do MMMM YYYY, H:mm')}
                    </div>
                    <div>
                        <strong>Poslední aktivita:</strong> {relativeTime(userSession.lastSeen)}
                    </div>
                </div>
            </div>
        );
    };

    let createUserSessions = (userSessions) => {
        return userSessions.map(createUserSession);
    };

    let renderPasswordLogin = () => {
        if (props.authentication.userProfile.loginMethods.password) {
            return (
                <div>
                    <Link to="/muj-ucet/nastaveni/zmenit-heslo" className="login-button"><span>Změnit <strong>heslo</strong></span></Link>
                </div>
            );
        }

        return (
            <div>
                <a className="login-button" href="#"><span>Nastavit <strong>heslo</strong></span></a>
            </div>
        );
    };

    return (
        <>
            <Helmet>
                <title>Můj účet</title>
                <meta name="robots" content="noindex, follow"/>
            </Helmet>
            <section className="page__section page__section--generic-heading">
                <div className="page__section__content">
                    <span className="page__section__subheading">&nbsp;</span>
                    <h2 className="page__section__heading">Nastavení účtu</h2>
                </div>
            </section>
            <section className="page__section page__section--user-profile">
                <div className="page__section__content">

                    <div className="reservation-form-wrapper">
                        <div className="reservation-form-sidebar">
                            <h3>Přihlašování</h3>

                            <FacebookConnect
                                appId={props.facebookAppId}
                                addAlert={props.addAlert}
                                api={props.api}
                                authentication={props.authentication}
                            />
                            <GoogleConnect
                                appId={props.googleAppId}
                                addAlert={props.addAlert}
                                api={props.api}
                                authentication={props.authentication}
                            />
                            {renderPasswordLogin()}

                        </div>
                        <div className="reservation-form">
                            <div className="reservation-form-heading">
                                <div>
                                    <h3>Přihlášená zařízení</h3>
                                    <div className="reservation-form-subheading">
                                        <span>Zde je seznam zařízení, která jsou přihlášená k vašemu účtu.</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                {createUserSessions(props.authentication.userSessions)}
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </>
    );
}

export default UserSettings;
