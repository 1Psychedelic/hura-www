import React from 'react'
import InputText from "../Form/InputText";

class UserProfileReservationInvoiceForm extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            'showForm': false,
            'form': {
                'name': {
                    'value': props.reservation.reservation.invoiceData?.name || '',
                    'validationError': ''
                },
                'ico': {
                    'value': props.reservation.reservation.invoiceData?.ico || '',
                    'validationError': ''
                },
                'dic': {
                    'value': props.reservation.reservation.invoiceData?.dic || '',
                    'validationError': ''
                },
                'city': {
                    'value': props.reservation.reservation.invoiceData?.city || '',
                    'validationError': ''
                },
                'street': {
                    'value': props.reservation.reservation.invoiceData?.street || '',
                    'validationError': ''
                },
                'zip': {
                    'value': props.reservation.reservation.invoiceData?.zip || '',
                    'validationError': ''
                },
                'notes': {
                    'value': props.reservation.reservation.invoiceData?.notes || '',
                    'validationError': ''
                },
                'submit': {
                    'value': 'Uložit',
                    'disabled': false
                },
                'focus': 'ico'
            }
        };

        this.onInputChange = this.onInputChange.bind(this);
        this.onInputRevalidate = this.onInputRevalidate.bind(this);
        this.onInputBlur = this.onInputBlur.bind(this);
        this.onSubmit = this.onSubmit.bind(this);

        this.ref = React.createRef();
        this.aresAbortController = new AbortController();
    }


    onInputChange(event) {
        const target = event.target;
        const value = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name;

        let formState = JSON.parse(JSON.stringify(this.state.form));
        formState[name].value = value;
        //formState.wasSubmitted.value = false;



        this.setState({
            'form': formState
        }, () => {
            if (this.state.form[name].validationError) {
                //this.validateField('form', name);
            }

            if (name === 'ico') {
                this.aresAbortController.abort();
                this.aresAbortController = new AbortController();
                this.props.api.request('GET', 'api/v1/ares/subject-by-ico?ico=' + value, null, (response, data) => {
                    let newState = JSON.parse(JSON.stringify(this.state.form));

                    if (response.status !== 200) {
                        return;
                    }

                    newState.name.value = newState.name.value || data.name;
                    newState.dic.value = newState.dic.value || data.dic;
                    newState.street.value = newState.street.value || data.street;
                    newState.city.value = newState.city.value || data.city;
                    newState.zip.value = newState.zip.value || data.zip;

                    this.setState({
                        'form': newState,
                    });
                }, undefined, this.aresAbortController.signal);
            }
        });
    }

    onInputRevalidate(event) {
        const target = event.target;
        const name = target.name;

        if (!this.state.form[name].validationError) {
            return;
        }

        this.onParentInputChange(event);
    }

    onInputBlur(event) {
        const target = event.target;
        const value = target.type === 'checkbox' ? target.checked : target.value;
        const name = target.name;

        let formState = JSON.parse(JSON.stringify(this.state.form));
        formState[name].value = value;
        //parentState[name].validationError = '';

        this.setState({
            'form': formState
        }, () => {
            //this.validateField('form', name);
        });
    }

    onSubmit(event) {
        event.preventDefault();

        let formState = JSON.parse(JSON.stringify(this.state.form));
        formState.submit.value = 'Počkejte prosím...';
        formState.submit.disabled = true;

        this.setState({'form': formState}, () => {
            let requestData = {
                'id': this.props.reservation.reservation.id,
                'name': this.state.form.name.value,
                'ico': this.state.form.ico.value,
                'dic': this.state.form.dic.value,
                'street': this.state.form.street.value,
                'city': this.state.form.city.value,
                'zip': this.state.form.zip.value,
                'notes': this.state.form.notes.value,
            }

            this.props.api.request('POST', 'api/v1/reservation/paying-on-invoice', requestData, (response, data) => {
                let newState = JSON.parse(JSON.stringify(this.state.form));
                newState.submit.value = 'Uložit';
                newState.submit.disabled = false;

                if (response.status !== 200) {
                    if (data.message) {
                        this.props.addAlert('invoiceForm', 'danger', data.message);
                    } else {
                        this.props.addAlert('invoiceForm', 'danger', 'Údaje o zaměstnavateli se nepodařilo uložit.');
                    }
                    this.setState({'form': newState});
                    return;
                }

                this.setState({
                    'form': newState,
                    'showForm': false
                });

                this.props.addAlert('invoiceForm', 'success', 'Děkujeme za vyplnění, fakturu Vám vystavíme co nejdříve to bude možné.');
                this.props.onReservationNotLoaded(this.props.reservation.reservation.id);
            });
        });
    }

    render() {
        let showForm = () => {
            this.setState({'showForm': true}, () => {
                if (this.ref && this.ref.current && this.ref.current.focus) {
                    this.ref.current.focus();
                }
            });
        };

        if (!this.props.initialLoadFinished) {
            return (
                <>
                </>
            );
        }

        if (!this.props.reservation.reservation.isPayingOnInvoice) {
            return (
                <>
                </>
            );
        }

        if (!this.props.reservation.reservation.invoiceData.isFilled && !this.state.showForm) {
            return (
                <div className="reservation-form-invoice-info">
                    <div className="text-center">
                        <a onClick={showForm} className="form-button form-button-inline">Vyplnit údaje o zaměstnavateli</a>
                    </div>
                </div>
            );
        }

        if (this.state.showForm) {
            return (
                <div className="reservation-form-invoice-info">
                    <div className="reservation-form-subheading"><span>Vyplňte IČO zaměstnavatele a my se pokusíme automaticky vyplnit zbytek údajů.</span></div>
                    <form onSubmit={this.onSubmit}>
                        <div className="form-half-width-container">
                            <div className="form-half-width">
                                <InputText ref={this.state.form.focus === 'ico' ? this.ref : null} name="ico" data={this.state.form.ico} onBlur={this.onInputBlur} onChange={this.onInputChange} onRevalidate={this.onInputRevalidate} required={true} label="IČO"/>
                                <InputText ref={this.state.form.focus === 'dic' ? this.ref : null} name="dic" data={this.state.form.dic} onBlur={this.onInputBlur} onChange={this.onInputChange} onRevalidate={this.onInputRevalidate} required={true} label="DIČ"/>
                                <InputText ref={this.state.form.focus === 'name' ? this.ref : null} name="name" data={this.state.form.name} onBlur={this.onInputBlur} onChange={this.onInputChange} onRevalidate={this.onInputRevalidate} required={true} label="Název firmy"/>
                            </div>
                            <div className="form-half-width">
                                <InputText ref={this.state.form.focus === 'street' ? this.ref : null} name="street" data={this.state.form.street} onBlur={this.onInputBlur} onChange={this.onInputChange} onRevalidate={this.onInputRevalidate} required={true} label="Ulice a číslo popisné"/>
                                <InputText ref={this.state.form.focus === 'city' ? this.ref : null} name="city" data={this.state.form.city} onBlur={this.onInputBlur} onChange={this.onInputChange} onRevalidate={this.onInputRevalidate} required={true} label="Město"/>
                                <InputText ref={this.state.form.focus === 'zip' ? this.ref : null} name="zip" data={this.state.form.zip} onBlur={this.onInputBlur} onChange={this.onInputChange} onRevalidate={this.onInputRevalidate} required={true} pattern="[0-9]{5}" label="PSČ"/>
                            </div>
                        </div>
                        <div className={'form-input-group form-input-group-textarea' + (this.state.form.notes.validationError ? ' form-error' : '')}>
                            <div className="form-input-group-inner">
                                <label htmlFor="notes">Poznámka k faktuře:</label>
                                <textarea ref={this.state.form.focus === 'notes' ? this.ref : null} name="notes" id="notes" onChange={this.onInputChange} onBlur={this.onInputBlur} defaultValue={this.state.form.notes.value} placeholder="Vyplňte zde jakékoliv specifické požadavky Vašeho zaměstnavatele k faktuře."/>
                            </div>
                            <div className="form-error-message">{this.state.form.notes.validationError}</div>
                        </div>
                        <div className="reservation-form-submits">
                            <div className="reservation-form-submits-left">
                            </div>
                            <div className="reservation-form-submits-right">
                                <input type="submit" className="form-button form-button-large" disabled={this.state.form.submit.disabled} value={this.state.form.submit.value}/>
                            </div>
                        </div>
                    </form>
                </div>
            );
        }

        return (
            <div className="reservation-form-finish-parent reservation-form-finish-group">
                <h4><strong>{this.props.reservation.reservation.invoiceData.name}</strong> (fakturační údaje)</h4>
                <div className="reservation-form-finish-parent-contact">
                    <div>
                        {this.props.reservation.reservation.invoiceData.street}<br/>
                        {this.props.reservation.reservation.invoiceData.city} {this.props.reservation.reservation.invoiceData.zip}
                    </div>
                    <div>
                        <div className="reservation-form-finish-parent-email">
                            <strong>IČO:</strong> {this.props.reservation.reservation.invoiceData.ico}
                        </div>
                        <div className="reservation-form-finish-parent-email">
                            <strong>DIČ:</strong> {this.props.reservation.reservation.invoiceData.dic}
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

export default UserProfileReservationInvoiceForm;
