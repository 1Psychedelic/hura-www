import React from 'react'
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
import { v4 as uuidv4 } from 'uuid';
import ReservationParentForm from "./ReservationParentForm";
import ReservationChildForm from "./ReservationChildForm";
import ReservationChildren from "./ReservationChildren";
import ReservationAddons from "./ReservationAddons";
import ReservationFinish from "./ReservationFinish";
import api from '../Api/Api'
import FormHelper from '../Helper/FormHelper'
import GoogleLogin from "../Authentication/GoogleLogin";


class ReservationForm extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            'initialLoadFinished': false,
            'parentForm': {
                'name': {
                    'value': props.authentication.userProfile?.name || '',
                    'validationError': ''
                },
                'phone': {
                    'value': props.authentication.userProfile?.phone || '',
                    'validationError': ''
                },
                'email': {
                    'value': props.authentication.userProfile?.email || '',
                    'validationError': ''
                },
                'street': {
                    'value': props.authentication.userProfile?.street || '',
                    'validationError': ''
                },
                'city': {
                    'value': props.authentication.userProfile?.city || '',
                    'validationError': ''
                },
                'zip': {
                    'value': props.authentication.userProfile?.zip || '',
                    'validationError': ''
                },
                'agreeGdpr': {
                    'value': false,
                    'validationError': ''
                },
                'agreeTermsAndConditions': {
                    'value': false,
                    'validationError': ''
                },
                'agreeNewsletter': {
                    'value': false,
                    'validationError': ''
                },
                'submit': {
                    'value': 'Uložit a pokračovat',
                    'disabled': false
                },
                'focus': null,
                'wasSubmitted': {
                    'value': false,
                    'validationError': ''
                },
            },
            'childForm': {
                'childId': {
                    'value': ''
                },
                'applicationChildId': {
                    'value': ''
                },
                'name': {
                    'value': '',
                    'validationError': ''
                },
                'gender': {
                    'value': '',
                    'validationError': '',
                    'options': [
                        {
                            'value': '',
                            'label': ''
                        },
                        {
                            'value': 'm',
                            'label': 'Chlapec'
                        },
                        {
                            'value': 'f',
                            'label': 'Dívka'
                        }
                    ]
                },
                'adhd': {
                    'value': '',
                    'validationError': '',
                    'options': [
                        {
                            'value': '',
                            'label': ''
                        },
                        {
                            'value': '1',
                            'label': 'Ano'
                        },
                        {
                            'value': '0',
                            'label': 'Ne'
                        }
                    ]
                },
                'dateBorn': {
                    'value': '',
                    'validationError': ''
                },
                'swimmer': {
                    'value': '',
                    'validationError': '',
                    'options': [
                        {
                            'value': '',
                            'label': ''
                        },
                        {
                            'value': '1',
                            'label': 'Plavec'
                        },
                        {
                            'value': '0',
                            'label': 'Neplavec'
                        }
                    ]
                },
                'firstTimer': {
                    'value': '',
                    'validationError': '',
                    'options': [
                        {
                            'value': '',
                            'label': ''
                        },
                        {
                            'value': '1',
                            'label': 'Ano'
                        },
                        {
                            'value': '0',
                            'label': 'Ne'
                        }
                    ]
                },
                'health': {
                    'value': '',
                    'validationError': ''
                },
                'submit': {
                    'value': 'Uložit a pokračovat',
                    'disabled': false
                },
                'focus': null
            },
            'children': [],
            'childrenForm': {
                'confirmation': {
                    'value': false,
                    'validationError': ''
                },
                'submit': {
                    'value': 'Uložit a pokračovat',
                    'disabled': false
                },
                'focus': null,
                'wasSubmitted': {
                    'value': false,
                    'validationError': ''
                }
            },
            'addons': [],
            'addonsForm': {
                'submit': {
                    'value': 'Uložit a pokračovat',
                    'disabled': false
                },
                'wasSubmitted': {
                    'value': false,
                    'validationError': ''
                }
            },
            'discountsForm': {
                'isPayingByCredit': {
                    'value': false,
                    'validationError': ''
                },
                'discountCode': {
                    'value': '',
                    'validationError': ''
                }
            },
            'discounts': {
                'canPayByCredit': true,
                'canUseDiscountCode': true,
                'payingByCredit': 0,
                'payingByDiscountCode': 0,
                'discountCode': ''
            },
            'finishForm': {
                'notes': {
                    'value': '',
                    'validationError': ''
                },
                'isPayingOnInvoice': {
                    'value': false,
                    'validationError': ''
                },
                'submit': {
                    'value': 'Dokončit rezervaci',
                    'disabled': false
                }
            }
        };

        this.ref = React.createRef();

        this.validators = {
            'parentForm': {
                'name': [
                    (component, value) => {
                        if (!value || !/(\S)\s(\S)/.test(value)) {
                            return 'Prosím vyplňte jméno a příjmení.';
                        }
                    }
                ],
                'phone': [
                    (component, value) => {
                        if (!value) {
                            return 'Prosím vyplňte telefonní číslo.';
                        }
                    }
                ],
                'email': [
                    (component, value) => {
                        if (!value) {
                            return 'Prosím vyplňte e-mailovou adresu.';
                        }
                    },
                    (component, value) => {
                        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                            return 'Zadaný e-mail není ve správném formátu.';
                        }
                    },
                    (component, value) => {
                        return this.props.api.request('POST', 'api/v1/reservation/check-email', {'email': value}, (response, data) => {
                            let wasAlreadyRegistered = data.wasAlreadyRegistered || false;
                            if (wasAlreadyRegistered) {
                                return 'Tento e-mail je již zaregistrovaný - použijte prosím přihlašovací formulář.';
                            }
                        });
                    }
                ],
                'street': [
                    (component, value) => {
                        if (!value) {
                            return 'Prosím vyplňte ulici a číslo domu.';
                        }
                    }
                ],
                'city': [
                    (component, value) => {
                        if (!value) {
                            return 'Prosím vyplňte město.';
                        }
                    }
                ],
                'zip': [
                    (component, value) => {
                        if (!value) {
                            return 'Prosím vyplňte PSČ.';
                        }

                        if (!/^[0-9]{5}$/.test(value.replace(/\s/g,''))) {
                            return 'Zadané PSČ není ve správném tvaru.';
                        }
                    }
                ],
                'agreeGdpr': [
                    (component, value) => {
                        if (!value) {
                            return 'Prosím potvrďte svůj souhlas se Zásadami ochrany osobních údajů.';
                        }
                    }
                ],
                'agreeTermsAndConditions': [
                    (component, value) => {
                        if (!value) {
                            return 'Prosím potvrďte svůj souhlas s VOP a s dokumentem Jak to u nás chodí.';
                        }
                    }
                ]
            },
            'childForm': {
                'name': [
                    (component, value) => {
                        if (!value || !/(\S)\s(\S)/.test(value)) {
                            return 'Prosím vyplňte jméno a příjmení.';
                        }
                    }
                ],
                'gender': [
                    (component, value) => {
                        if (!value) {
                            return 'Prosím vyberte pohlaví dítěte.';
                        }
                    }
                ],
                'adhd': [
                    (component, value) => {
                        if (!value) {
                            return 'Prosím vyberte, zda má dítě ADHD či podobnou diagnózu.';
                        }
                    }
                ],
                'dateBorn': [
                    (component, value) => {
                        if (!value) {
                            return 'Prosím zadejte datum narození dítěte.';
                        }

                        let eventEnd = dayjs(this.props.event.ends, 'YYYY-MM-DD hh:mm:ss');
                        let dateBorn = dayjs(value, 'YYYY-MM-DD').hour(23).minute(59).second(59);

                        let age = eventEnd.diff(dateBorn, 'years');

                        if (age > this.props.event.ageCap || age < this.props.event.ageMin) {
                            return 'Věk dítěte ' + age + ' let je mimo povolený věkový rozsah ' + this.props.event.ageMin + '-' + this.props.event.ageMax + ' let.';
                        }
                    }
                ],
                'swimmer': [
                    (component, value) => {
                        if (!value) {
                            return 'Prosím vyberte, zda umí dítě plavat.';
                        }
                    }
                ],
                'firstTimer': [
                    (component, value) => {
                        if (!value) {
                            return 'Prosím vyberte, zda jede dítě poprvé na tábor.';
                        }
                    }
                ],
                'health': [
                    (component, value) => {
                        if (!value) {
                            return 'Prosím popište co nejpodrobněji zdravotní stav dítěte.';
                        }
                    }
                ]
            },
            'childrenForm': {
                'confirmation': [
                    (component, value) => {
                        if (!value) {
                            return 'Prosím potvrďte, že informace odpovídají skutečnosti.';
                        }
                    }
                ]
            }
        };

        this.childrenAbortController = new AbortController();

        this.storageEventListener = (e) => {
            if (e.key !== this.props.url) {
                return;
            }

            this.loadFromLocalStorage();
        };

        this.onParentInputChange = this.onParentInputChange.bind(this);
        this.onParentSubmit = this.onParentSubmit.bind(this);
        this.onParentInputRevalidate = this.onParentInputRevalidate.bind(this);
        this.onParentInputBlur = this.onParentInputBlur.bind(this);
        this.onChildInputChange = this.onChildInputChange.bind(this);
        this.onChildInputBlur = this.onChildInputBlur.bind(this);
        this.onChildSubmit = this.onChildSubmit.bind(this);
        this.onChildAddToReservation = this.onChildAddToReservation.bind(this);
        this.onChildRemoveFromReservation = this.onChildRemoveFromReservation.bind(this);
        this.onChildEdit = this.onChildEdit.bind(this);
        this.onChildAdd = this.onChildAdd.bind(this);
        this.onChildrenInputChange = this.onChildrenInputChange.bind(this);
        this.onChildrenSubmit = this.onChildrenSubmit.bind(this);
        this.onAuth = this.onAuth.bind(this);
        this.onAddonAdd = this.onAddonAdd.bind(this);
        this.onAddonSubtract = this.onAddonSubtract.bind(this);
        this.onAddonsSubmit = this.onAddonsSubmit.bind(this);
        this.onSetDiscountCode = this.onSetDiscountCode.bind(this);
        this.onPayByCredit = this.onPayByCredit.bind(this);
        this.onFinishInputBlur = this.onFinishInputBlur.bind(this);
        this.onFinishInputChange = this.onFinishInputChange.bind(this);
        this.onFinishSubmit = this.onFinishSubmit.bind(this);
        this.onChildChanged = this.onChildChanged.bind(this);
        this.getReservationFromApi = this.getReservationFromApi.bind(this);
    }

    getReservationFromApi() {
        this.props.api.request('GET', 'api/v1/reservation/view?eventId=' + encodeURIComponent(this.props.event.id), {}, (response, data) => {
            //this.setState(newState);

            if (response.status !== 200 && response.status !== 204) {
                if (data.message) {
                    this.props.addAlert('reservationForm', 'danger', data.message);
                }

                this.setState({'initialLoadFinished': true});
                return;
            }

            if (response.status === 200) {
                let parentState = JSON.parse(JSON.stringify(this.state.parentForm));
                parentState.name.value = data.parent.name;
                parentState.phone.value = data.parent.phone;
                parentState.email.value = data.parent.email;
                parentState.street.value = data.parent.street;
                parentState.city.value = data.parent.city;
                parentState.zip.value = data.parent.zip;
                parentState.agreeGdpr.value = !!data.parent.agreeGdpr;
                parentState.agreeTermsAndConditions.value = !!data.parent.agreeTermsAndConditions;
                parentState.submit.disabled = false;
                parentState.submit.value = 'Uložit a pokračovat';

                let addonsFormState = JSON.parse(JSON.stringify(this.state.addonsForm));
                addonsFormState.submit.disabled = false;
                addonsFormState.submit.value = 'Uložit a pokračovat';

                let childrenFormState = JSON.parse(JSON.stringify(this.state.childrenForm));
                childrenFormState.submit.disabled = false;
                childrenFormState.submit.value = 'Uložit a pokračovat';

                let childFormState = JSON.parse(JSON.stringify(this.state.childForm));
                childFormState.submit.disabled = false;
                childFormState.submit.value = 'Uložit a pokračovat';

                let discountsState = JSON.parse(JSON.stringify(this.state.discounts));
                discountsState.canPayByCredit = data.discounts.canPayByCredit;
                discountsState.canUseDiscountCode = data.discounts.canUseDiscountCode;
                discountsState.payingByCredit = data.discounts.payingByCredit;
                discountsState.payingByDiscountCode = data.discounts.payingByDiscountCode;
                discountsState.discountCode = data.discounts.discountCode;

                let discountsFormState = JSON.parse(JSON.stringify(this.state.discountsForm));
                discountsFormState.isPayingByCredit.value = data.discounts.payingByCredit > 0;
                discountsFormState.discountCode.value = data.discounts.discountCode;

                let newState = {
                    'initialLoadFinished': true,
                    'parentForm': parentState,
                    'children': data.children,
                    'addons': data.addons,
                    'childrenForm': childrenFormState,
                    'childForm': childFormState,
                    'addonsForm': addonsFormState,
                    'discounts': discountsState,
                    'discountsForm': discountsFormState
                };

                this.setState(newState);
            }
        });
    }

    componentDidMount() {
        this.loadFromLocalStorage();

        this.getReservationFromApi();

        window.addEventListener('storage', this.storageEventListener);
    }

    componentWillUnmount() {
        window.removeEventListener('storage', this.storageEventListener);
    }

    handleValidationResult(formName, fieldName, result, resolve, reject) {
        if (!result) {
            resolve({
                'component': this,
                'form': formName,
                'field': fieldName,
                'value': this.state[formName][fieldName].value,
                'validationError': ''
            });
        } else if (result.then) {
            result.then((innerResult) => {
                this.handleValidationResult(formName, fieldName, innerResult, resolve, reject);
            });
        } else {
            reject({
                'component': this,
                'form': formName,
                'field': fieldName,
                'value': this.state[formName][fieldName].value,
                'validationError': result
            });
        }
    }

    validateField(formName, fieldName) {
        let fieldPromises = [];
        for (let i in this.validators[formName][fieldName]) {
            fieldPromises.push(new Promise((resolve, reject) => {
                let errorMessage = this.validators[formName][fieldName][i](this, this.state[formName][fieldName].value);
                this.handleValidationResult(formName, fieldName, errorMessage, resolve, reject);
            }));
        }

        Promise.all(fieldPromises)
            .then((result) => {
                let formState = {};
                formState[formName] = this.state[formName];
                formState[formName][fieldName].validationError = '';
                this.setState(formState);
            })
            .catch((result) => {
                let formState = {};
                formState[formName] = this.state[formName];
                formState[formName][fieldName].validationError = result.validationError;
                this.setState(formState);
            });

        this.props.dismissAlert('formValidation');
    }

    validateForm(formName) {
        let formState = {};
        formState[formName] = JSON.parse(JSON.stringify(this.state[formName]));
        formState[formName].submit.value = 'Počkejte prosím...';
        formState[formName].submit.disabled = true;
        this.setState(formState);

        let allPromises = [];
        if (this.validators && this.validators[formName]) {
            for (let fieldName in this.validators[formName]) {
                let fieldPromises = [];
                for (let i in this.validators[formName][fieldName]) {
                    fieldPromises.push(new Promise((resolve, reject) => {
                        let errorMessage = this.validators[formName][fieldName][i](this, this.state[formName][fieldName].value);
                        this.handleValidationResult(formName, fieldName, errorMessage, resolve, reject);
                    }));
                }
                allPromises.push(Promise.all(fieldPromises));
            }
        }

        return Promise.allSettled(allPromises).then((results) => {
            let failed = false;

            let newState = {};
            newState[formName] = JSON.parse(JSON.stringify(this.state[formName]));
            newState[formName].focus = null;

            for (let i in results) {
                if (results[i].status === 'rejected') {
                    console.log(results[i]);
                    newState[formName][results[i].reason.field].validationError = results[i].reason.validationError;
                    failed = true;
                    if (!newState[formName].focus) {
                        newState[formName].focus = results[i].reason.field;
                    }
                } else if (results[i].status === 'fulfilled') {
                    newState[formName][results[i].value[0].field].validationError = '';
                }
            }

            this.setState(newState);

            if (this.ref && this.ref.current && this.ref.current.focus) {
                this.ref.current.focus();
            }

            if (failed) {
                this.props.addAlert('formValidation', 'danger', 'Ve formuláři jsou chyby, opravte je prosím a zkuste formulář odeslat znovu.');
                newState[formName].submit.value = 'Uložit a pokračovat';
                newState[formName].submit.disabled = false;
            }

            return !failed;
        });
    }

    onParentSubmit(e) {
        e.preventDefault();

        this.validateForm('parentForm')
            .then((isSuccess) => {
                let newState = {};
                newState['parentForm'] = JSON.parse(JSON.stringify(this.state['parentForm']));
                newState['parentForm'].submit.value = 'Uložit a pokračovat';
                newState['parentForm'].submit.disabled = false;

                if (isSuccess) {
                    this.props.api.request('POST', 'api/v1/reservation/parent', {
                        'eventId': this.props.event.id,
                        'name': this.state.parentForm.name.value,
                        'email': this.state.parentForm.email.value,
                        'phone': this.state.parentForm.phone.value,
                        'street': this.state.parentForm.street.value,
                        'city': this.state.parentForm.city.value,
                        'zip': this.state.parentForm.zip.value,
                        'agreeGdpr': this.state.parentForm.agreeGdpr.value,
                        'agreeTermsAndConditions': this.state.parentForm.agreeTermsAndConditions.value
                    }, (response, data) => {
                        if (response.status !== 201) {
                            if (data.message) {
                                this.props.addAlert('reservationForm', 'danger', data.message);
                            }

                            this.setState(newState);
                            return;
                        }
                        newState['parentForm'].wasSubmitted.value = true;
                        this.setState(newState, () => {
                            this.saveToLocalStorage();
                            if (this.state.children.length === 0) {
                                this.props.history.replace(this.props.url + '/dite');
                            } else {
                                this.props.history.replace(this.props.url + '/deti');
                            }
                        });
                    }/*, (data) => {
                        this.onAuth(data);
                    }*/);
                } else {
                    this.setState(newState);
                }
            });
    }

    onParentInputBlur(event) {
        const target = event.target;
        const value = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name;

        let parentState = JSON.parse(JSON.stringify(this.state.parentForm));
        parentState[name].value = value;
        //parentState[name].validationError = '';

        this.setState({
            'parentForm': parentState
        }, () => {
            this.saveToLocalStorage();
            this.validateField('parentForm', name);
        });
    }

    onParentInputChange(event) {
        const target = event.target;
        const value = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name;

        let parentState = JSON.parse(JSON.stringify(this.state.parentForm));
        parentState[name].value = value;
        parentState.wasSubmitted.value = false;

        this.setState({
            'parentForm': parentState
        }, () => {
            this.saveToLocalStorage();

            if (this.state.parentForm[name].validationError) {
                this.validateField('parentForm', name);
            }
        });
    }

    onParentInputRevalidate(event) {
        const target = event.target;
        const name = target.name;

        if (!this.state.parentForm[name].validationError) {
            return;
        }

        this.onParentInputChange(event);
    }

    onChildSubmit(e) {
        e.preventDefault();

        let newState = {};
        newState['childForm'] = JSON.parse(JSON.stringify(this.state['childForm']));
        newState['childForm'].submit.value = 'Uložit a pokračovat';
        newState['childForm'].submit.disabled = false;

        this.validateForm('childForm')
            .then((isSuccess) => {
                if (!isSuccess) {
                    this.setState(newState);
                    return;
                }

                let currentChildState = JSON.parse(JSON.stringify(this.state.childForm));

                let child = {
                    'isInReservation': true,
                    'childId': currentChildState.childId.value || uuidv4(),
                    'applicationChildId': currentChildState.applicationChildId.value || uuidv4(),
                    'name': currentChildState.name.value,
                    'gender': currentChildState.gender.value,
                    'adhd': FormHelper.convertSelectValueToBoolean(currentChildState.adhd.value),
                    'dateBorn': currentChildState.dateBorn.value,
                    'swimmer': FormHelper.convertSelectValueToBoolean(currentChildState.swimmer.value),
                    'firstTimer': FormHelper.convertSelectValueToBoolean(currentChildState.firstTimer.value),
                    'health': currentChildState.health.value,
                };

                this.props.api.request('POST', 'api/v1/reservation/child', {
                    'eventId': this.props.event.id,
                    'email': this.state.parentForm.email.value,
                    'childId': child.childId,
                    'applicationChildId': child.applicationChildId,
                    'name': child.name,
                    'gender': child.gender,
                    'adhd': child.adhd,
                    'dateBorn': child.dateBorn,
                    'swimmer': child.swimmer,
                    'firstTimer': child.firstTimer,
                    'health': child.health
                }, (response, data) => {
                    if (response.status !== 201) {
                        if (data.message) {
                            this.props.addAlert('reservationForm', 'danger', data.message);
                        }
                        this.setState(newState);
                        return;
                    }
                    let stateChildren = JSON.parse(JSON.stringify(this.state.children));
                    let found = false;
                    for (let i in stateChildren) {
                        if (stateChildren[i].childId === child.childId || stateChildren[i].applicationChildId === child.applicationChildId) {
                            child.childId = data.childId;
                            child.applicationChildId = data.applicationChildId;

                            stateChildren[i] = child;
                            found = true;
                            break;
                        }
                    }

                    if (!found) {
                        child.childId = data.childId;
                        child.applicationChildId = data.applicationChildId;
                        stateChildren.push(child);
                    }

                    let emptiedChild = JSON.parse(JSON.stringify(currentChildState));
                    for (let i in emptiedChild) {
                        if (i !== 'submit' && emptiedChild[i] && emptiedChild[i].hasOwnProperty('value')) {
                            emptiedChild[i].value = '';
                        }
                    }

                    newState.childForm = emptiedChild;
                    newState.children = stateChildren;

                    this.onChildChanged();
                    this.props.onChildrenChange(stateChildren);
                    this.setState(newState, () => {
                        this.saveToLocalStorage();
                    });

                    this.props.history.replace(this.props.url + '/deti');
                });
            });
    }

    onChildInputBlur(event) {
        const target = event.target;
        const value = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name;

        let childState = JSON.parse(JSON.stringify(this.state.childForm));
        childState[name].value = value;
        //parentState[name].validationError = '';

        this.setState({
            'childForm': childState
        }, () => {
            this.saveToLocalStorage();
            this.validateField('childForm', name);
        });
    }

    onChildInputChange(event) {
        const target = event.target;
        const value = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name;
        let childState = JSON.parse(JSON.stringify(this.state.childForm));
        childState[name].value = value;

        this.setState({
            'childForm': childState
        }, () => {
            this.saveToLocalStorage();

            console.log({'childInputChange': this.state.childForm});

            if (this.state.childForm[name].validationError) {
                this.validateField('childForm', name);
            }
        });
    }

    onChildRemoveFromReservation(childId) {
        if (this.state.childrenForm.submit.disabled) {
            return;
        }

        let childrenState = JSON.parse(JSON.stringify(this.state.children));
        for (let i in childrenState) {
            if (childrenState[i].childId === childId) {
                childrenState[i].isInReservation = false;
            }
        }

        this.props.api.request('POST', 'api/v1/reservation/child-remove-from-reservation', {
            'eventId': this.props.event.id,
            'email': this.state.parentForm.email.value,
            'childId': childId
        }, (response, data) => {
            if (response.status !== 200) {
                if (data.message) {
                    this.props.addAlert('reservationForm', 'danger', data.message);
                }
                return;
            } else {
                childrenState = data.children;
                this.props.onChildrenChange(childrenState);
                this.setState({
                    'children': childrenState
                }, () => {
                    this.saveToLocalStorage();
                });
            }
        }, undefined, this.childrenAbortController.signal)
            .catch((e) => {
                // e.name === 'AbortError'
                console.log(e.message);
            });
    }

    onChildAddToReservation(childId) {
        if (this.state.childrenForm.submit.disabled) {
            return;
        }

        let childrenState = JSON.parse(JSON.stringify(this.state.children));
        for (let i in childrenState) {
            if (childrenState[i].childId === childId) {
                childrenState[i].isInReservation = true;
                childrenState[i].applicationChildId = uuidv4();
            }
        }

        this.props.api.request('POST', 'api/v1/reservation/child-add-to-reservation', {
            'eventId': this.props.event.id,
            'email': this.state.parentForm.email.value,
            'childId': childId
        }, (response, data) => {
            if (response.status !== 200) {
                if (data.message) {
                    this.props.addAlert('reservationForm', 'danger', data.message);
                }
                return;
            } else {
                childrenState = data.children;
                this.onChildChanged();
                this.props.onChildrenChange(childrenState);
                this.setState({
                    'children': childrenState
                }, () => {
                    this.saveToLocalStorage();
                });
            }
        }, undefined, this.childrenAbortController.signal);
    }

    onChildEdit(childId) {
        let childState = JSON.parse(JSON.stringify(this.state.childForm));

        let childToEdit = null;
        for (let i in this.state.children) {
            if (this.state.children[i].childId === childId) {
                childToEdit = JSON.parse(JSON.stringify(this.state.children[i]));
                console.log({
                    'childToEdit': childToEdit,
                    'stateChild': this.state.children[i]
                });
                break;
            }
        }

        if (!childToEdit) {
            return;
        }
        /*let child = {
            'isInReservation': true,
            'childId': currentChildState.childId.value || uuidv4(),
            'applicationChildId': currentChildState.applicationChildId.value || uuidv4(),
            'name': currentChildState.name.value,
            'gender': currentChildState.gender.value,
            'adhd': FormHelper.convertSelectValueToBoolean(currentChildState.adhd.value),
            'dateBorn': currentChildState.dateBorn.value,
            'swimmer': FormHelper.convertSelectValueToBoolean(currentChildState.swimmer.value),
            'firstTimer': FormHelper.convertSelectValueToBoolean(currentChildState.firstTimer.value),
            'health': currentChildState.health.value,
        };*/

        let formValues = {
            'childId': childToEdit.childId,
            'applicationChildId': childToEdit.applicationChildId,
            'name': childToEdit.name.toString(),
            'gender': childToEdit.gender.toString(),
            'adhd': FormHelper.convertBooleanToSelectValue(childToEdit.adhd),
            'dateBorn': childToEdit.dateBorn?.toString(),
            'swimmer': FormHelper.convertBooleanToSelectValue(childToEdit.swimmer),
            'firstTimer': FormHelper.convertBooleanToSelectValue(childToEdit.firstTimer),
            'health': childToEdit.health.toString(),
        };

        for (let i in childToEdit) {
            if (childState[i] && childState[i].hasOwnProperty('value')) {
                childState[i].value = formValues[i];
                childState[i].validationError = '';
            }
        }

        childState.submit.value = 'Uložit a pokračovat';
        childState.submit.disabled = false;

        this.setState({
            'childForm': childState
        }, () => {
            this.saveToLocalStorage();
            this.props.history.replace(this.props.url + '/dite');
        });
    }

    onChildAdd(e) {
        e.preventDefault();

        let childState = JSON.parse(JSON.stringify(this.state.childForm));
        for (let i in childState) {
            if (childState[i] && childState[i].hasOwnProperty('value')) {
                childState[i].value = '';
                childState[i].validationError = '';
            }
        }
        childState.submit.value = 'Uložit a pokračovat';
        childState.submit.disabled = false;
        childState.focus = 'name';

        this.setState({
            'childForm': childState
        }, () => {
            this.saveToLocalStorage();
            this.props.history.replace(this.props.url + '/dite');

            setTimeout(() => {
                if (this.ref && this.ref.current && this.ref.current.focus) {
                    this.ref.current.focus();
                }
            }, 50);
        });
    }

    onChildChanged() {
        this.setState({
            'childrenForm': {
                'confirmation': {
                    'value': false,
                    'validationError': ''
                },
                'submit': {
                    'value': 'Uložit a pokračovat',
                    'disabled': false
                },
                'wasSubmitted': {
                    'value': false,
                    'validationError': ''
                },
                'focus': null
            }
        });
    }

    onChildrenInputChange() {
        const target = event.target;
        const value = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name;
        let childrenFormState = JSON.parse(JSON.stringify(this.state.childrenForm));
        childrenFormState[name].value = value;

        this.setState({
            'childrenForm': childrenFormState
        }, () => {
            this.saveToLocalStorage();

            if (this.state.childrenForm[name].validationError) {
                this.validateField('childrenForm', name);
            }
        });
    }

    onChildrenSubmit(e) {
        e.preventDefault();

        this.childrenAbortController.abort();
        this.childrenAbortController = new AbortController();

        this.validateForm('childrenForm')
            .then((isSuccess) => {
                let newState = {};
                newState['childrenForm'] = JSON.parse(JSON.stringify(this.state['childrenForm']));
                newState['childrenForm'].submit.value = 'Uložit a pokračovat';
                newState['childrenForm'].submit.disabled = false;

                if (!isSuccess) {
                    this.setState(newState);
                    return;
                }

                this.props.api.request('POST', 'api/v1/reservation/children', {
                    'eventId': this.props.event.id,
                    'email': this.state.parentForm.email.value,
                    'children': this.state.children
                }, (response, data) => {
                    if (response.status !== 200) {
                        if (data.message) {
                            this.props.addAlert('reservationForm', 'danger', data.message);
                        }
                        this.setState(newState);
                        return;
                    } else {
                        newState.children = data.children;
                        newState.childrenForm.wasSubmitted.value = true;
                        this.setState(newState, () => {
                            this.saveToLocalStorage();
                            this.props.history.replace(this.props.url + '/doplnkove-sluzby');
                        });
                    }
                });
            });
    }

    onAddonAdd(addonId) {
        if (this.state.addonsForm.submit.disabled) {
            return;
        }

        let addonsState = JSON.parse(JSON.stringify(this.state.addons));

        if (!addonsState[addonId]) {
            addonsState[addonId] = 1;
        } else {
            if (addonsState[addonId] >= this.state.children.filter((child) => child.isInReservation).length) {
                addonsState[addonId] = this.state.children.filter((child) => child.isInReservation).length;
            } else {
                addonsState[addonId] = Math.max(0, addonsState[addonId]) + 1;
            }
        }

        if (this.state.children.filter((child) => child.isInReservation).length === 0) {
            addonsState[addonId] = 0;
        }

        this.setState({
            'addons': addonsState
        }, () => {
            this.saveToLocalStorage();
        });
    }

    onAddonSubtract(addonId) {
        if (this.state.addonsForm.submit.disabled) {
            return;
        }

        let addonsState = JSON.parse(JSON.stringify(this.state.addons));

        if (!addonsState[addonId]) {
            addonsState[addonId] = 0;
        } else {
            if (addonsState[addonId] <= 0) {
                addonsState[addonId] = 0;
            } else {
                addonsState[addonId] = (Math.min(this.state.children.filter((child) => child.isInReservation).length, addonsState[addonId])) - 1;
            }
        }

        if (this.state.children.filter((child) => child.isInReservation).length === 0) {
            addonsState[addonId] = 0;
        }

        this.setState({
            'addons': addonsState
        }, () => {
            this.saveToLocalStorage();
        });
    }

    onAddonsSubmit() {
        let addonsState = JSON.parse(JSON.stringify(this.state.addons));
        for (let addonId in addonsState) {
            addonsState[addonId] = Math.min(Math.max(0, addonsState[addonId]), this.state.children.filter((child) => child.isInReservation).length);
        }

        let addonsFormState = JSON.parse(JSON.stringify(this.state.addonsForm));
        addonsFormState.submit.disabled = true;
        addonsFormState.submit.value = 'Počkejte prosím...';

        this.setState({
            'addons': addonsState,
            'addonsForm': addonsFormState
        }, () => {
            this.props.api.request('POST', 'api/v1/reservation/addons', {
                'eventId': this.props.event.id,
                'addons': this.state.addons
            }, (response, data) => {
                let newState = {};
                newState['addonsForm'] = JSON.parse(JSON.stringify(this.state['addonsForm']));
                newState['addonsForm'].submit.value = 'Uložit a pokračovat';
                newState['addonsForm'].submit.disabled = false;

                if (response.status !== 200) {
                    if (data.message) {
                        this.props.addAlert('reservationForm', 'danger', data.message);
                    }
                    this.setState(newState);

                    return;
                } else {
                    newState.addons = data.addons;
                    newState.addonsForm.wasSubmitted.value = true;
                }

                this.setState(newState,  () => {
                    this.saveToLocalStorage();
                    this.props.history.replace(this.props.url + '/dokonceni');
                });
            });
        });
    }

    onPayByCredit(payByCredit) {
        if (!this.state.discounts.canPayByCredit) {
            return;
        }

        this.props.api.request('POST', 'api/v1/reservation/set-pay-by-credit', {
            'eventId': this.props.event.id,
            'payByCredit': payByCredit
        }, (response, data) => {
            if (response.status !== 200) {
                if (data.message) {
                    this.props.addAlert('reservationForm', 'danger', data.message);
                }
                return;
            } else {

                let discountsState = JSON.parse(JSON.stringify(this.state.discounts));
                if (!payByCredit) {
                    discountsState.payingByCredit = 0;
                } else {
                    discountsState.payingByCredit = data.payingByCredit;
                }

                this.setState({
                    'discounts': discountsState
                }, () => {
                    this.saveToLocalStorage();
                });
            }
        }/*, undefined, this.childrenAbortController.signal*/);
    }

    onSetDiscountCode(discountCode) {
        if (!this.state.discounts.canUseDiscountCode) {
            return;
        }

        this.props.api.request('POST', 'api/v1/reservation/set-discount-code', {
            'eventId': this.props.event.id,
            'discountCode': discountCode
        }, (response, data) => {
            let discountsState = JSON.parse(JSON.stringify(this.state.discounts));
            if (response.status !== 200) {
                if (data.message) {
                    this.props.addAlert('reservationForm', 'danger', data.message);
                }
                discountsState.payingByDiscountCode = 0;
                discountsState.discountCode = '';
            } else {
                if (data.message) {
                    this.props.addAlert('reservationForm', 'success', data.message);
                }
                discountsState.payingByDiscountCode = data.payingByDiscountCode;
                discountsState.discountCode = data.discountCode;

            }
            this.setState({
                'discounts': discountsState
            }, () => {
                this.saveToLocalStorage();
            });
        }/*, undefined, this.childrenAbortController.signal*/);
    }

    onFinishInputChange(event) {
        const target = event.target;
        const value = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name;
        let finishFormState = JSON.parse(JSON.stringify(this.state.finishForm));
        finishFormState[name].value = value;

        this.setState({
            'finishForm': finishFormState
        }, () => {
            this.saveToLocalStorage();
        });
    }

    onFinishInputBlur(event) {
        const target = event.target;
        const value = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name;
        let finishFormState = JSON.parse(JSON.stringify(this.state.finishForm));
        finishFormState[name].value = value;

        this.setState({
            'finishForm': finishFormState
        }, () => {
            this.saveToLocalStorage();
        });
    }

    onFinishSubmit(event) {
        event.preventDefault();

        let newState = {};
        newState['finishForm'] = JSON.parse(JSON.stringify(this.state['finishForm']));
        newState['finishForm'].submit.value = 'Počkejte prosím...';
        newState['finishForm'].submit.disabled = true;

        this.setState(newState, () => {
            let requestData = {
                'eventId': this.props.event.id,
                'parent': {
                    'name': this.state.parentForm.name.value,
                    'email': this.state.parentForm.email.value,
                    'phone': this.state.parentForm.phone.value,
                    'street': this.state.parentForm.street.value,
                    'city': this.state.parentForm.city.value,
                    'zip': this.state.parentForm.zip.value,
                    'agreeGdpr': this.state.parentForm.agreeGdpr.value,
                    'agreeTermsAndConditions': this.state.parentForm.agreeTermsAndConditions.value
                },
                'children': this.state.children,
                'addons': this.state.addons,
                'notes': this.state.finishForm.notes.value,
                'isPayingOnInvoice': this.state.finishForm.isPayingOnInvoice.value
            }

            this.props.api.request('POST', 'api/v1/reservation/finish', requestData, (response, data) => {
                let newState = {};
                newState['finishForm'] = JSON.parse(JSON.stringify(this.state['finishForm']));
                newState['finishForm'].submit.value = 'Dokončit rezervaci';
                newState['finishForm'].submit.disabled = false;

                if (response.status !== 201 || !data.applicationId) {
                    if (data.message) {
                        this.props.addAlert('reservationForm', 'danger', data.message);
                    }
                    this.setState(newState,  () => {
                        this.saveToLocalStorage();
                    });
                    return;
                }

                let applicationId = data.applicationId;

                this.clearLocalStorage();

                this.props.history.replace('/muj-ucet/rezervace/' + encodeURIComponent(applicationId) + '/odeslano');
            })
        });
    }

    clearLocalStorage() {
        localStorage.removeItem(this.props.url);
    }

    saveToLocalStorage() {
        console.log({'saving': this.state, 'key': this.props.url});
        localStorage.setItem(this.props.url, JSON.stringify(this.state));
    }

    loadFromLocalStorage() {
        let value = localStorage.getItem(this.props.url);
        console.log({'url': this.props.url, 'value': value});
        if (value) {
            try {
                let currentState = JSON.parse(JSON.stringify(this.state));
                let state = JSON.parse(value);
                if (state) {
                    for (var form in currentState) {
                        if (!state.hasOwnProperty(form)) {
                            continue;
                        }

                        if (form === 'children') {
                            currentState.children = state.children || currentState.children;
                            continue;
                        }

                        if (form === 'addons') {
                            currentState.addons = state.addons || currentState.addons;
                            continue;
                        }

                        if (form === 'discounts') {
                            currentState.discounts = state.discounts || currentState.discounts;
                        }

                        for (var field in currentState[form]) {
                            if (!state[form].hasOwnProperty(field)) {
                                continue;
                            }

                            for (var property in currentState[form][field]) {
                                if (!state[form][field].hasOwnProperty(property)) {
                                    continue;
                                }

                                if (property === 'validationError' || property === 'options') {
                                    continue;
                                }

                                currentState[form][field][property] = state[form][field][property] === undefined ? currentState[form][field][property] : state[form][field][property];
                            }
                        }
                    }

                    console.log({'loading': currentState, 'key': this.props.url});
                    this.setState(currentState);
                }
            } catch (e) {
                localStorage.removeItem(this.props.url);
            }
        }
    }

    static getDerivedStateFromProps(props, state) {
        let currentState = JSON.parse(JSON.stringify(state));

        if (props.authentication.userChildren) {
            for (let i in props.authentication.userChildren) {
                if (!props.authentication.userChildren[i]) {
                    continue;
                }

                let found = false;

                for (let j in currentState.children) {
                    if (!currentState.children[j]) {
                        continue;
                    }

                    if (currentState.children[j].childId === props.authentication.userChildren[i].childId) {
                        currentState.children[j].name = props.authentication.userChildren[i].name;
                        currentState.children[j].gender = props.authentication.userChildren[i].gender;
                        currentState.children[j].adhd = props.authentication.userChildren[i].adhd;
                        currentState.children[j].dateBorn = props.authentication.userChildren[i].dateBorn;
                        currentState.children[j].swimmer = props.authentication.userChildren[i].swimmer;
                        currentState.children[j].firstTimer = props.authentication.userChildren[i].firstTimer;
                        currentState.children[j].health = props.authentication.userChildren[i].health;
                        found = true;
                    }
                }

                if (!found) {
                    currentState.children.push({
                        'isInReservation': false,
                        'childId': props.authentication.userChildren[i].childId,
                        'applicationChildId': uuidv4(),
                        'name': props.authentication.userChildren[i].name,
                        'gender': props.authentication.userChildren[i].gender,
                        'adhd': props.authentication.userChildren[i].adhd,
                        'dateBorn': props.authentication.userChildren[i].dateBorn,
                        'swimmer': props.authentication.userChildren[i].swimmer,
                        'firstTimer': props.authentication.userChildren[i].firstTimer,
                        'health': props.authentication.userChildren[i].health,
                    });
                }
            }
        }

        return currentState;
    }

    onAuth(data) {
        console.log({'onAuth': data});
        this.props.onAuth(data);


        if (!data.authentication.isLoggedIn || !data.authentication.userProfile) {
            return;
        }

        let stateParent = JSON.parse(JSON.stringify(this.state.parentForm));
        console.log(stateParent);
        for (var i in stateParent) {
            /*if (stateParent[i].value) {
                stateParent[i].validationError = '';
                continue;
            }*/

            if (stateParent[i] != null && stateParent[i].hasOwnProperty('value') && data.authentication.userProfile[i]) {
                stateParent[i].value = data.authentication.userProfile[i] || null;
                stateParent[i].validationError = '';
            }

        }

        this.setState({'parentForm': stateParent});
    }

    render() {
        //if (this.state.redirectTo !== null) {
            //let redirectTo = this.state.redirectTo;
            //this.setState({'redirectTo': null});

            //return (
            //    <Redirect to={`${this.props.url}${redirectTo}`}/>
            //);
        //}

        let links = {
            'parent': {
                'title': 'Rodiče',
                'url': `${this.props.url}`,
                'isEnabled': true,
                'key': 'parent'
            },
            'children': {
                'title': 'Děti',
                'url': `${this.props.url}/deti`,
                'isEnabled': this.state.parentForm.wasSubmitted.value,
                'key': 'children'
            },
            'addons': {
                'title': 'Doplňky',
                'url': `${this.props.url}/doplnkove-sluzby`,
                'isEnabled': this.state.parentForm.wasSubmitted.value && this.state.childrenForm.wasSubmitted.value,
                'key': 'addons'
            },
            'finish': {
                'title': 'Dokončení',
                'url': `${this.props.url}/dokonceni`,
                'isEnabled': this.state.parentForm.wasSubmitted.value && this.state.childrenForm.wasSubmitted.value && this.state.addonsForm.wasSubmitted.value,
                'key': 'finish'
            },
        };

        return (
            <Switch>
                <Route exact path={`${this.props.url}`}>
                    <ReservationParentForm
                        ref={this.ref}
                        form={this.state.parentForm}
                        onSubmit={this.onParentSubmit}
                        onChange={this.onParentInputChange}
                        onRevalidate={this.onParentInputRevalidate}
                        api={this.props.api}
                        onBlur={this.onParentInputBlur}
                        authentication={this.props.authentication}
                        links={links}
                        reservationFormTop={this.props.reservationFormTop}
                        scrollManager={this.props.scrollManager}
                        addAlert={this.props.addAlert}
                        website={this.props.website}
                    />
                </Route>
                <Route path={`${this.props.url}/dite`}>
                    <ReservationChildForm
                        ref={this.ref}
                        children={this.state.children}
                        form={this.state.childForm}
                        onChange={this.onChildInputChange}
                        onBlur={this.onChildInputBlur}
                        onSubmit={this.onChildSubmit}
                        onChildAddToReservation={this.onChildAddToReservation}
                        onChildRemoveFromReservation={this.onChildRemoveFromReservation}
                        authentication={this.props.authentication}
                        links={links}
                        reservationFormTop={this.props.reservationFormTop}
                        scrollManager={this.props.scrollManager}
                        url={this.props.url}
                        history={this.props.history}
                        addAlert={this.props.addAlert}
                    />
                </Route>
                <Route path={`${this.props.url}/deti`}>
                    <ReservationChildren
                        ref={this.ref}
                        children={this.state.children}
                        onChange={this.onChildrenInputChange}
                        onChildAddToReservation={this.onChildAddToReservation}
                        onChildRemoveFromReservation={this.onChildRemoveFromReservation}
                        onChildAdd={this.onChildAdd}
                        onChildEdit={this.onChildEdit}
                        onSubmit={this.onChildrenSubmit}
                        authentication={this.props.authentication}
                        form={this.state.childrenForm}
                        links={links}
                        reservationFormTop={this.props.reservationFormTop}
                        scrollManager={this.props.scrollManager}
                        url={this.props.url}
                        history={this.props.history}
                        addAlert={this.props.addAlert}
                    />
                </Route>
                <Route path={`${this.props.url}/doplnkove-sluzby`}>
                    <ReservationAddons
                        event={this.props.event}
                        countChildren={this.state.children.filter((child) => child.isInReservation).length}
                        addons={this.state.addons}
                        form={this.state.addonsForm}
                        reservation={this.state}
                        onAddonAdd={this.onAddonAdd}
                        onAddonSubtract={this.onAddonSubtract}
                        onSubmit={this.onAddonsSubmit}
                        onPayByCredit={this.onPayByCredit}
                        onSetDiscountCode={this.onSetDiscountCode}
                        authentication={this.props.authentication}
                        links={links}
                        reservationFormTop={this.props.reservationFormTop}
                        scrollManager={this.props.scrollManager}
                        url={this.props.url}
                        history={this.props.history}
                        addAlert={this.props.addAlert}
                    />
                </Route>
                <Route path={`${this.props.url}/dokonceni`}>
                    <ReservationFinish
                        event={this.props.event}
                        addons={this.state.addons}
                        form={this.state.finishForm}
                        onSubmit={this.onFinishSubmit}
                        onChange={this.onFinishInputChange}
                        onBlur={this.onFinishInputBlur}
                        onPayByCredit={this.onPayByCredit}
                        onSetDiscountCode={this.onSetDiscountCode}
                        authentication={this.props.authentication}
                        reservation={this.state}
                        links={links}
                        reservationFormTop={this.props.reservationFormTop}
                        scrollManager={this.props.scrollManager}
                        url={this.props.url}
                        history={this.props.history}
                        addAlert={this.props.addAlert}
                    />
                </Route>
            </Switch>
        );
    }
};

export default withRouter(ReservationForm)
