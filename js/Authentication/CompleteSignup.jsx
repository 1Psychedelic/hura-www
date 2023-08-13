import React from 'react'
import {Helmet} from "react-helmet";
import FacebookConnect from "../Authentication/FacebookConnect";
import GoogleConnect from "../Authentication/GoogleConnect";
import SetPassword from "./SetPassword";

const CompleteSignup = (props) => {

    if (!props.authentication.isLoggedIn) {
        return (
            <>
                <section className="page__section page__section--generic-heading">
                    <div className="page__section__content">
                        <span className="page__section__subheading">&nbsp;</span>
                        <h2 className="page__section__heading">Aktivovat účet</h2>
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

    let renderPasswordLogin = () => {
        if (props.authentication.userProfile.loginMethods.password) {
            return (
                <div>
                    <div className="login-button disabled"><span>Heslo je nastavené</span></div>
                </div>
            );
        }

        return (
            <div>
                <SetPassword
                    authentication={props.authentication}
                    api={props.api}
                    onPasswordSet={props.onPasswordSet}
                    addAlert={props.addAlert}
                />
            </div>
        );
    };

    return (
        <>
            <Helmet>
                <title>Aktivovat účet</title>
                <meta name="robots" content="noindex, follow"/>
            </Helmet>
            <section className="page__section page__section--generic-heading">
                <div className="page__section__content">
                    <span className="page__section__subheading">&nbsp;</span>
                    <h2 className="page__section__heading">Aktivovat účet</h2>
                </div>
            </section>
            <section className="page__section page__section--user-profile">
                <div className="page__section__content">

                    <div className="reservation-form-wrapper compact">
                        <div className="reservation-form">
                            <div className="reservation-form-heading">
                                <div>
                                    <h3>Nastavte si heslo</h3>
                                    <div className="reservation-form-subheading">
                                        <span>nebo využijte k přihlašování svůj účet Facebook či Google.</span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                {renderPasswordLogin()}
                            </div>
                            <div>
                                <div>
                                    <FacebookConnect
                                        appId={props.facebookAppId}
                                        addAlert={props.addAlert}
                                        api={props.api}
                                        authentication={props.authentication}
                                    />
                                </div>
                                <div>
                                    <GoogleConnect
                                        appId={props.googleAppId}
                                        addAlert={props.addAlert}
                                        api={props.api}
                                        authentication={props.authentication}
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </>
    );
}

export default CompleteSignup;
