import React from 'react'
import dayjs from "dayjs";


class EventDetailDiscountText extends React.Component {

    countdownInterval = null;

    constructor(props) {
        super(props);

        let days = 0;
        let hours = 0;
        let minutes = 0;
        let seconds = 0;

        if (props.discountExpiresAt) {
            let diff = dayjs(props.discountExpiresAt).diff(dayjs(), 'milliseconds');

            days = Math.floor(diff / (1000 * 60 * 60 * 24));
            hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            seconds = Math.floor((diff % (1000 * 60)) / 1000);
        }

        this.state = {
            'discountExpiresAt': props.discountExpiresAt || null,
            'countdown': {
                'days': days,
                'hours': hours,
                'minutes': minutes,
                'seconds': seconds
            }
        };

        let countdownTick = () => {
            let diff = dayjs(this.state.discountExpiresAt).diff(dayjs(), 'milliseconds');

            var days = Math.floor(diff / (1000 * 60 * 60 * 24));
            var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((diff % (1000 * 60)) / 1000);

            if (diff <= 0) {
                props.onDiscountExpired();
                this.setState({
                    'countdown': {
                        'days': 0,
                        'hours': 0,
                        'minutes': 0,
                        'seconds': 0
                    }
                });
            } else {
                this.setState({
                    'countdown': {
                        'days': days,
                        'hours': hours,
                        'minutes': minutes,
                        'seconds': seconds
                    }
                });
            }
        };

        countdownTick();
        this.countdownInterval = setInterval(countdownTick, 1000);
    }

    componentWillUnmount() {
        clearInterval(this.countdownInterval);
    }

    static getDerivedStateFromProps(props, state) {
        if (props.discountExpiresAt) {
            let diff = dayjs(props.discountExpiresAt).diff(dayjs(), 'milliseconds');

            let days = Math.floor(diff / (1000 * 60 * 60 * 24));
            let hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            let minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            let seconds = Math.floor((diff % (1000 * 60)) / 1000);

            return {
                'discountExpiresAt': props.discountExpiresAt || null,
                'countdown': {
                    'days': days,
                    'hours': hours,
                    'minutes': minutes,
                    'seconds': seconds
                }
            };
        }

        return {
            'discountExpiresAt': null,
            'countdown': {
                'days': 0,
                'hours': 0,
                'minutes': 0,
                'seconds': 0
            }
        }
    }

    render() {
        if (!this.state.discountExpiresAt || (this.state.countdown.days === 0 && this.state.countdown.hours === 0 && this.state.countdown.minutes === 0 && this.state.countdown.seconds === 0)) {
            return (<></>);
        }

        let expirationText = 'Sleva končí za';

        if (this.state.countdown.days > 0) {
            expirationText += ' ' + this.state.countdown.days + ' dní';
        }
        if (this.state.countdown.hours > 0) {
            expirationText += ' ' + this.state.countdown.hours + ' hodin';
        }
        if (this.state.countdown.minutes > 0) {
            expirationText += ' ' + this.state.countdown.minutes + ' minut';
        }
        if (this.state.countdown.days === 0 && this.state.countdown.hours === 0 && this.state.countdown.seconds > 0) {
            expirationText += ' ' + this.state.countdown.seconds + ' vteřin';
        }

        expirationText += '!';

        return (
            <div className="event-detail-info-discount-expiration">
                {expirationText}
            </div>
        );
    }
};

export default EventDetailDiscountText
