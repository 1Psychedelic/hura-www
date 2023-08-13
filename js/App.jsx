import React from 'react'
import {
    BrowserRouter as Router,
    Switch,
    Route,
    Link
} from "react-router-dom";
import Parser from 'html-react-parser';

import MobileMenu from "./Layout/MobileMenu";
import TopMenu from "./Layout/TopMenu";
import MainMenu from "./Layout/MainMenu";
import Homepage from "./Homepage/Homepage";
import Footer from "./Layout/Footer";
import Events from "./EventList/Events";
import ScrollToTop from "./ReactHelper/ScrollToTop";
import EventDetail from "./EventDetail/EventDetail";
import {Helmet} from "react-helmet";

import Reservation from "./Reservation/Reservation";
import Crash from "./Miscellaneous/Crash";
import api from "./Api/Api"
import Error404 from "./Error/Error404";
import ScrollManagerWrapper from "./ReactHelper/ScrollManagerWrapper";
import ScrollManager from "./ReactHelper/ScrollManager";
import UserProfileReservation from "./UserProfile/UserProfileReservation";
import UserProfile from "./UserProfile/UserProfile";
import UserSettings from "./UserProfile/UserSettings";
import Alerts from "./Alerts/Alerts";
import AlertsAutoDismiss from "./Alerts/AlertsAutoDismiss";
import ErrorBoundary from "./Error/ErrorBoundary";
import Contact from "./Contact/Contact";
import CompleteSignup from "./Authentication/CompleteSignup";
import ChangePassword from "./UserProfile/ChangePassword";
import RequestResetPasswordLink from "./Authentication/RequestResetPasswordLink";
import ResetPassword from "./Authentication/ResetPassword";
import Registration from "./Authentication/Registration";
import StaticPage from "./StaticPage/StaticPage";
import CookiesPopup from "./Consent/CookiesPopup";
import Games from "./Pages/Games";

class App extends React.Component {

    refreshNotificationsInterval = undefined;

    constructor(props) {
        super(props);
        this.state = {
            'initialLoadFinished': props.initialState.initialLoadFinished || false,
            'homepage': props.initialState.homepage || {
                'enabledSections': [],
                'events': [],
                'reviews': [],
                'fairytales': [],
                'nextEvent': null,
                'archiveEvents': []
            },
            'website': props.initialState.website || {
                'name': 'Hurá tábory, z.s.',
                'title': 'Hurá tábory, z.s.',
                'heading': 'Letní a víkendové tábory pro děti!',
                'slogan': 'Dopřejte svým dětem zábavu během celého roku!',
                'description': '',
                'keywords': '',
                'email': 'info@hura-tabory.cz',
                'phone': '',
                'phoneHumanReadable': '',
                'bankAccount': '',
                'facebookLink': '',
                'instagramLink': '',
                'pinterestLink': '',
                'address': '',
                'addressLink': '',
                'termsAndConditions': '',
                'gdpr': '',
                'rules': '',
                'contactPerson': '',
                'ico': '',
                'bankName': '',
                'orgDescription': '',
                'google': {
                    'appId': ''
                },
                'facebook': {
                    'appId': ''
                },
                'menu': {
                    'top': [],
                    'main': [],
                    'mobile': [],
                    'footer': []
                },
                'javascripts': []
            },
            'events': props.initialState.events || {
                'camps': [],
                'trips': []
            },
            'loadedEvents': props.initialState.loadedEvents || {},
            'games': props.initialState.games || [],
            'authentication': props.initialState.authentication || {
                'loggedIn': undefined,
                'accessToken': undefined,
                'userChildren': [],
                'reservations': {},
                'reservationListPages': {},
                'reservationListLastPage': 1,
            },
            'loadedStaticPages': props.initialState.loadedStaticPages || {},
            'notifications': props.initialState.notifications || {
                'countNew': 0,
                'autoRefresh': false
            }
        };

        this.onAuth = this.onAuth.bind(this);
        this.onNotification = this.onNotification.bind(this);
        this.onChildrenChange = this.onChildrenChange.bind(this);
        this.onEventNotLoaded = this.onEventNotLoaded.bind(this);
        this.onReservationNotLoaded = this.onReservationNotLoaded.bind(this);
        this.onStaticPageNotLoaded = this.onStaticPageNotLoaded.bind(this);
        this.onReservationListNotLoaded = this.onReservationListNotLoaded.bind(this);
        this.onUserSessionsChanged = this.onUserSessionsChanged.bind(this);
        this.addAlert = this.addAlert.bind(this);
        this.dismissAlert = this.dismissAlert.bind(this);
        this.dismissAllAlerts = this.dismissAllAlerts.bind(this);
        this.onPasswordSet = this.onPasswordSet.bind(this);
        this.renderJavascripts = this.renderJavascripts.bind(this);

        this.refPageTop = React.createRef();
        this.refReservationFormTop = React.createRef();
        this.refScrollManager = React.createRef();
        this.refAlerts = React.createRef();

        this.api = api;
        this.api.setOnAuthCallback(this.onAuth);
        this.api.setOnNotificationCallback(this.onNotification);
        if (this.state.authentication.accessToken !== undefined) {
            this.api.setAccessToken(this.state.authentication.accessToken);
        }

        this.onNotification({'notifications': this.state.notifications});
    }

    addAlert(key, type, message) {
        if (this.refAlerts.current) {
            this.refAlerts.current.addAlert(key, type, message);
        }
    }

    dismissAlert(key) {
        if (this.refAlerts.current) {
            this.refAlerts.current.dismissAlert(key);
        }
    }

    dismissAllAlerts() {
        if (this.refAlerts.current) {
            this.refAlerts.current.dismissAllAlerts();
        }
    }

    componentWillUnmount() {
        clearInterval(this.refreshNotificationsInterval);
    }

    componentDidMount() {
        //console.log(data);
        //this.setState(data);
// + (this.state.authentication.accessToken ? '&accessToken=' + this.state.authentication.accessToken : '')
        var app = this;

        if (this.state.authentication.isLoggedIn === undefined) {
            this.api.request('GET', 'api/v1/authentication/user', {}, (response, data) => {
                app.setState(data);
            })
            .then(() => {
                this.api.request('GET', 'api/v1/home/view', {}, (response, data) => {
                    app.setState(data);
                }).catch((response) => {
                    this.addAlert(
                        'loadHomepage',
                        'danger',
                        response.message || 'Při pokusu o načtení stránky došlo k chybě. Zkuste prosím stránku načíst znovu.'
                    );
                });
            })
            .catch((response) => {
                this.addAlert(
                    'loadHomepage',
                    'danger',
                    response.message || 'Při pokusu o přihlášení došlo k chybě. Zkuste prosím stránku načíst znovu.'
                );
            });
        }

        let flashMessageId = (new URLSearchParams(window.location.search)).get('fid');
        if (flashMessageId) {
            this.api.request('GET', 'api/v1/flash-message?hash=' + flashMessageId, {}, (response, data) => {
                if (data.type && data.message) {
                    this.addAlert('flash', data.type, data.message);
                }
            });
        }
    }

    onEventNotLoaded(path, callback) {
        this.api.request('GET', 'api/v1/event/view?path=' + encodeURIComponent(path), {}, (response, data) => {
            let loadedEventsState = JSON.parse(JSON.stringify(this.state.loadedEvents));
            if (response.status === 404) {
                loadedEventsState[path] = 404;
            } else if (response.status === 200 && data) {
                loadedEventsState[data.url] = data;
            } else {
                loadedEventsState[path] = 500;
            }

            if (!callback) {
                this.setState({'loadedEvents': loadedEventsState});
            } else {
                this.setState({'loadedEvents': loadedEventsState}, callback);
            }
        }).catch(function (response) {
            console.log(response);
        });
    }

    onReservationListNotLoaded(page) {
        api.request('GET', 'api/v1/user-profile/reservation/list?page=' + encodeURIComponent(page), {}, (response, data) => {
            let authenticationState = JSON.parse(JSON.stringify(this.state.authentication));
            authenticationState.reservations = authenticationState.reservations || {};
            if (response.status === 200 && data && data.reservations) {
                for (let i in data.reservations) {
                    authenticationState.reservations[i] = data.reservations[i];
                }
                authenticationState.reservationListPages[page] = data.reservations;
                authenticationState.reservationListLastPage = page;
            } else {
                //authenticationState.reservations[id.toString()] = 500;
                //alert('error');
            }

            this.setState({'authentication': authenticationState});
        }).catch(function (response) {
            console.log(response);
        });
    }

    onReservationNotLoaded(id) {
        this.api.request('GET', 'api/v1/user-profile/reservation/view?reservationId=' + encodeURIComponent(id), {}, (response, data) => {
            let authenticationState = JSON.parse(JSON.stringify(this.state.authentication));
            authenticationState.reservations = authenticationState.reservations || {};
            if (response.status === 404) {
                authenticationState.reservations[id.toString()] = 404;
            } else if (response.status === 200 && data) {
                authenticationState.reservations[id.toString()] = data;
            } else {
                authenticationState.reservations[id.toString()] = 500;
            }

            this.setState({'authentication': authenticationState});
        }).catch(function (response) {
            console.log(response);
        });
    }

    onStaticPageNotLoaded(path, slug, callback) {
        this.api.request('GET', 'api/v1/static-page/view?slug=' + encodeURIComponent(slug), {}, (response, data) => {
            let loadedStaticPagesState = JSON.parse(JSON.stringify(this.state.loadedStaticPages));
            if (response.status === 404) {
                loadedStaticPagesState[path] = 404;
            } else if (response.status === 200 && data) {
                loadedStaticPagesState[path] = data;
            } else {
                loadedStaticPagesState[path] = 500;
            }

            if (!callback) {
                this.setState({'loadedStaticPages': loadedStaticPagesState});
            } else {
                this.setState({'loadedStaticPages': loadedStaticPagesState}, callback);
            }
        }).catch(function (response) {
            console.log(response);
        });
    }

    onAuth(data) {
        if (data.authentication) {
            if (!data.authentication.isLoggedIn) {
                localStorage.clear();
            }
            this.setState({
                'authentication': data.authentication
            });
        }
    }

    onNotification(data) {
        if (data.notifications) {
            if (data.notifications.autoRefresh && this.refreshNotificationsInterval === undefined) {
                this.refreshNotificationsInterval = setInterval(() => {
                    this.api.request('GET', 'api/v1/notifications/view');
                }, 60000);
            } else if (!data.notifications.autoRefresh) {
                clearInterval(this.refreshNotificationsInterval);
                this.refreshNotificationsInterval = undefined;
            }

            this.setState({
                'notifications': data.notifications
            });
        }
    }

    onPasswordSet() {
        if (!this.state.authentication || !this.state.authentication.isLoggedIn) {
            return;
        }

        let authenticationState = JSON.parse(JSON.stringify(this.state.authentication));
        authenticationState.userProfile.loginMethods.password = true;

        this.setState({
            'authentication': authenticationState
        });
    }

    onChildrenChange(data) {
        let authenticationState = JSON.parse(JSON.stringify(this.state.authentication));
        for (let j in data) {
            if (!data[j] || !data[j].hasOwnProperty('childId')) {
                continue;
            }
            if (!authenticationState.userChildren) {
                authenticationState.userChildren = [];
            }

            let edited = false;
            for (let i in authenticationState.userChildren) {
                if (!authenticationState.userChildren[i] || !authenticationState.userChildren[i].hasOwnProperty('childId')) {
                    continue;
                }

                if (data[j].childId === authenticationState.userChildren[i].childId) {
                    authenticationState.userChildren[i] = {
                        'childId': data[j].childId,
                        'name': data[j].name,
                        'gender': data[j].gender,
                        'adhd': data[j].adhd,
                        'dateBorn': data[j].dateBorn,
                        'swimmer': data[j].swimmer,
                        'firstTimer': data[j].firstTimer,
                        'health': data[j].health,
                    };
                    edited = true;
                }
            }

            if (!edited) {
                authenticationState.userChildren.push({
                    'childId': data[j].childId,
                    'name': data[j].name,
                    'gender': data[j].gender,
                    'adhd': data[j].adhd,
                    'dateBorn': data[j].dateBorn,
                    'swimmer': data[j].swimmer,
                    'firstTimer': data[j].firstTimer,
                    'health': data[j].health,
                });
            }
        }

        this.setState({
            'authentication': authenticationState
        });

        console.log({'childrenChanged': authenticationState});
    }

    onUserSessionsChanged(userSessions) {
        let authenticationState = JSON.parse(JSON.stringify(this.state.authentication));
        authenticationState.userSessions = userSessions;
        this.setState({
            'authentication': authenticationState
        });
    }

    renderJavascript(item) {
        return (
            <>
                {Parser(item.code)}
            </>
        );
    }

    renderJavascripts() {
        if (!this.state.initialLoadFinished) {
            return (
                <>
                </>
            );
        }

        return this.state.website.javascripts.map(this.renderJavascript);
    }

    render() {
        let titleTemplate = '%s · ' + this.state.website.title;
        let defaultTitle = this.state.website.title;
        if (this.state.notifications.countNew > 0) {
            titleTemplate = '(' + this.state.notifications.countNew + ') %s · ' + this.state.website.title;
             defaultTitle = '(' + this.state.notifications.countNew + ') ' + this.state.website.title;
        }

        let canonical = window.location.href.split('?')[0];
        if (canonical.endsWith('/')) {
            canonical = canonical.slice(0, -1);
        }

        return (
            <ErrorBoundary>
                <Router>

                    <Helmet
                        defaultTitle={defaultTitle}
                        titleTemplate={titleTemplate}
                    >
                        <meta name="description" content={this.state.website.description}/>
                        <meta name="keywords" content={this.state.website.keywords}/>
                        <meta name="robots" content="index, follow"/>
                        <meta name="author" content={this.state.website.name}/>
                        <link rel="canonical" href={canonical}/>

                        {this.renderJavascripts()}

                    </Helmet>

                    <Alerts
                        ref={this.refAlerts}
                    />

                    <MobileMenu
                        menu={this.state.website.menu.mobile}
                    />
                    <TopMenu
                        api={this.api}
                        authentication={this.state.authentication}
                        addAlert={this.addAlert}
                        website={this.state.website}
                        googleAppId={this.state.website.google.appId}
                        facebookAppId={this.state.website.facebook.appId}
                        initialLoadFinished={this.state.initialLoadFinished}
                        menu={this.state.website.menu.top}
                        notifications={this.state.notifications}
                    />

                    <div className="page-wrapper">
                        <div className="page">
                            <AlertsAutoDismiss
                                dismissAllAlerts={this.dismissAllAlerts}
                            />
                            <MainMenu
                                menu={this.state.website.menu.main}
                            />
                            <ScrollManagerWrapper
                                ref={this.refScrollManager}
                            />
                            <div className="page-scroll-top" ref={this.refPageTop}/>
                            <Switch>

                                <Route exact path="/">
                                    <Homepage
                                        homepage={this.state.homepage}
                                        games={this.state.games}
                                        initialLoadFinished={this.state.initialLoadFinished}
                                        refPageTop={this.refPageTop}
                                        scrollManager={this.refScrollManager}
                                        reservationFormTop={this.refReservationFormTop}
                                        slogan={this.state.website.slogan}
                                        heading={this.state.website.heading}
                                        api={this.api}
                                        addAlert={this.addAlert}
                                    />
                                </Route>
                                <Route path="/crash">
                                    <ErrorBoundary>
                                        <Crash/>
                                    </ErrorBoundary>
                                </Route>
                                <Route path="/tabory">
                                    <Events
                                        events={this.state.events.camps}
                                        heading="Dětské tábory"
                                        subheading="Letní tábory · Jarní tábory · Podzimní tábory"
                                        subheadingEmpty="Aktuálně nemáme naplánované žádné tábory."
                                        scrollManager={this.refScrollManager}
                                        reservationFormTop={this.refReservationFormTop}
                                        initialLoadFinished={this.state.initialLoadFinished}
                                    />
                                </Route>
                                <Route path="/vylety">
                                    <Events
                                        events={this.state.events.trips}
                                        heading="Víkendovky"
                                        subheadingEmpty="Aktuálně nemáme naplánované žádné výlety."
                                        scrollManager={this.refScrollManager}
                                        reservationFormTop={this.refReservationFormTop}
                                        initialLoadFinished={this.state.initialLoadFinished}
                                    />
                                </Route>
                                <Route path="/tabor/:slug/rezervace">
                                    <Reservation
                                        reservationFormTop={this.refReservationFormTop}
                                        scrollManager={this.refScrollManager}
                                        events={this.state.events.camps}
                                        api={this.api}
                                        onChildrenChange={this.onChildrenChange}
                                        authentication={this.state.authentication}
                                        initialLoadFinished={this.state.initialLoadFinished}
                                        addAlert={this.addAlert}
                                        dismissAlert={this.dismissAlert}
                                        website={this.state.website}
                                    />
                                </Route>
                                <Route path="/tabor/:slug/:tab?">
                                    <EventDetail
                                        scrollManager={this.refScrollManager}
                                        reservationFormTop={this.refReservationFormTop}
                                        events={this.state.events.camps.concat(this.state.events.trips)}
                                        loadedEvents={this.state.loadedEvents}
                                        initialLoadFinished={this.state.initialLoadFinished}
                                        onEventNotLoaded={this.onEventNotLoaded}
                                        api={this.api}
                                        addAlert={this.addAlert}
                                    />
                                </Route>
                                <Route path="/vylet/:slug/rezervace">
                                    <Reservation
                                        reservationFormTop={this.refReservationFormTop}
                                        scrollManager={this.refScrollManager}
                                        events={this.state.events.trips}
                                        api={this.api}
                                        onChildrenChange={this.onChildrenChange}
                                        authentication={this.state.authentication}
                                        initialLoadFinished={this.state.initialLoadFinished}
                                        addAlert={this.addAlert}
                                        dismissAlert={this.dismissAlert}
                                        website={this.state.website}
                                    />
                                </Route>
                                <Route path="/vylet/:slug/:tab?">
                                    <EventDetail
                                        scrollManager={this.refScrollManager}
                                        reservationFormTop={this.refReservationFormTop}
                                        events={this.state.events.trips.concat(this.state.events.camps)}
                                        loadedEvents={this.state.loadedEvents}
                                        initialLoadFinished={this.state.initialLoadFinished}
                                        onEventNotLoaded={this.onEventNotLoaded}
                                        api={this.api}
                                        addAlert={this.addAlert}
                                    />
                                </Route>
                                <Route path="/muj-ucet/rezervace/:id/:context?">
                                    <UserProfileReservation
                                        reservationFormTop={this.refReservationFormTop}
                                        onReservationNotLoaded={this.onReservationNotLoaded}
                                        reservations={this.state.authentication.reservations || {}}
                                        initialLoadFinished={this.state.initialLoadFinished}
                                        website={this.state.website}
                                        addAlert={this.addAlert}
                                        api={this.api}
                                    />
                                </Route>
                                <Route exact path="/muj-ucet/nastaveni/zmenit-heslo">
                                    <ChangePassword
                                        authentication={this.state.authentication}
                                        initialLoadFinished={this.state.initialLoadFinished}
                                        addAlert={this.addAlert}
                                        api={this.api}
                                    />
                                </Route>
                                <Route exact path="/muj-ucet/nastaveni">
                                    <UserSettings
                                        authentication={this.state.authentication}
                                        initialLoadFinished={this.state.initialLoadFinished}
                                        onUserSessionsChanged={this.onUserSessionsChanged}
                                        addAlert={this.addAlert}
                                        facebookAppId={this.state.website.facebook.appId}
                                        googleAppId={this.state.website.google.appId}
                                        api={this.api}
                                    />
                                </Route>
                                <Route exact path="/muj-ucet">
                                    <UserProfile
                                        authentication={this.state.authentication}
                                        initialLoadFinished={this.state.initialLoadFinished}
                                        onReservationListNotLoaded={this.onReservationListNotLoaded}
                                    />
                                </Route>
                                <Route exact path="/kontakty">
                                    <Contact
                                        authentication={this.state.authentication}
                                        initialLoadFinished={this.state.initialLoadFinished}
                                        website={this.state.website}
                                        addAlert={this.addAlert}
                                        api={this.api}
                                    />
                                </Route>
                                <Route exact path="/aktivovat-ucet">
                                    <CompleteSignup
                                        authentication={this.state.authentication}
                                        initialLoadFinished={this.state.initialLoadFinished}
                                        onUserSessionsChanged={this.onUserSessionsChanged}
                                        addAlert={this.addAlert}
                                        facebookAppId={this.state.website.facebook.appId}
                                        googleAppId={this.state.website.google.appId}
                                        api={this.api}
                                        onPasswordSet={this.onPasswordSet}
                                    />
                                </Route>
                                <Route exact path="/obnovit-zapomenute-heslo">
                                    <RequestResetPasswordLink
                                        authentication={this.state.authentication}
                                        initialLoadFinished={this.state.initialLoadFinished}
                                        addAlert={this.addAlert}
                                        api={this.api}
                                    />
                                </Route>
                                <Route exact path="/nastavit-nove-heslo">
                                    <ResetPassword
                                        authentication={this.state.authentication}
                                        initialLoadFinished={this.state.initialLoadFinished}
                                        addAlert={this.addAlert}
                                        api={this.api}
                                    />
                                </Route>
                                <Route exact path="/zaregistrovat-se">
                                    <Registration
                                        authentication={this.state.authentication}
                                        initialLoadFinished={this.state.initialLoadFinished}
                                        addAlert={this.addAlert}
                                        api={this.api}
                                    />
                                </Route>
                                <Route exact path="/nase-stolni-hry">
                                    <Games
                                        initialLoadFinished={this.state.initialLoadFinished}
                                        games={this.state.games}
                                    />
                                </Route>
                                <Route exact path="/stranka/:slug">
                                    <StaticPage
                                        initialLoadFinished={this.state.initialLoadFinished}
                                        reservationFormTop={this.refReservationFormTop}
                                        onStaticPageNotLoaded={this.onStaticPageNotLoaded}
                                        loadedStaticPages={this.state.loadedStaticPages}
                                    />
                                </Route>
                                <Route>
                                    <Error404/>
                                </Route>
                            </Switch>
                            <Footer
                                website={this.state.website}
                                api={this.api}
                                addAlert={this.addAlert}
                                menu={this.state.website.menu.footer}
                            />
                            <CookiesPopup
                            />
                        </div>
                    </div>
                </Router>
            </ErrorBoundary>
        );
    }
};

export default App
