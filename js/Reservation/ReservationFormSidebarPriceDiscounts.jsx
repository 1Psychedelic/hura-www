import React from 'react'

class ReservationFormSidebarPriceDiscounts extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            'canPayByCredit': props.event.allowCredits && props.reservation.discounts.canPayByCredit,
            'canUseDiscountCode': props.event.allowDiscountCodes && props.reservation.discounts.canUseDiscountCode,
            'payingByCredit': props.reservation.discounts.payingByCredit,
            'payingByDiscountCode': props.reservation.discounts.payingByDiscountCode,
            'discountCode': props.reservation.discountCode,
            'showDiscountCodeForm': false
        };
    }

    static getDerivedStateFromProps(props, state) {
        let currentState = JSON.parse(JSON.stringify(state));
        currentState.canPayByCredit = props.event.allowCredits && props.reservation.discounts.canPayByCredit;
        currentState.canUseDiscountCode = props.event.allowDiscountCodes && props.reservation.discounts.canUseDiscountCode;
        currentState.payingByCredit = props.reservation.discounts.payingByCredit;
        currentState.payingByDiscountCode = props.reservation.discounts.payingByDiscountCode;
        currentState.discountCode = currentState.discountCode || props.reservation.discountCode;

        return currentState;
    }

    render() {

        let createPayByCreditButton = () => {
            if (!this.state.canPayByCredit) {
                return (
                    <>
                    </>
                );
            }

            if (this.state.payingByCredit !== 0) {
                return (
                    <a onClick={() => {this.props.onPayByCredit(false)}}>Zrušit platbu kreditem</a>
                );
            }

            return (
                <a onClick={() => {this.props.onPayByCredit(true)}}>Zaplatit kreditem</a>
            );
        };

        let createDiscountCodeForm = () => {
            if (!this.state.canUseDiscountCode) {
                return (
                    <>
                    </>
                );
            }

            if (!this.state.showDiscountCodeForm) {
                return (
                    <a onClick={() => {this.setState({'showDiscountCodeForm': true})}}>Použít slevový kód</a>
                );
            }

            let onFormSubmit = (e) => {
                e.preventDefault();

                console.log(this.state);

                this.props.onSetDiscountCode(this.state.discountCode);
                this.setState({
                    'showDiscountCodeForm': false
                });
            };

            return (
                <form onSubmit={onFormSubmit}>
                    <div className={'form-input-group form-input-group-text' + (this.state.discountCode ? ' active' : '')}>
                        <div className={'form-input-group-inner'}>
                            <label htmlFor="reservation-sidebar-price-discount-code">Slevový kód</label>
                            <input onChange={(e) => {console.log(e);this.setState({'discountCode': e.target.value})}} id="reservation-sidebar-price-discount-code" name="discountCode" type="text" value={this.state.discountCode}/>
                        </div>
                    </div>
                    <div>
                        <input type="submit" className="login-button login-button-password" value="Použít slevový kód"/>
                    </div>
                </form>
            );
        };

        return (
            <div>
                <div>
                    {createPayByCreditButton()}
                </div>
                <div>
                    {createDiscountCodeForm()}
                </div>
            </div>
        );
    }

};

export default ReservationFormSidebarPriceDiscounts;
