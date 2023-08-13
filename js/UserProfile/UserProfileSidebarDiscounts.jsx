import React from 'react'

const UserProfileSidebarDiscounts = (props) => {
    if (!props.authentication.isLoggedIn) {
        return (
            <>
            </>
        );
    }

    let renderExpiration = (expiration) => {
        return (
            <div key={expiration.expiration}>
                {expiration.amount} Kč má platnost do {expiration.expiration}.
            </div>
        );
    };

    let renderExpirations = () => {
        return props.authentication.credits.expirations.map(renderExpiration);
    };

    let renderCredits = () => {
        if (props.authentication.credits.total <= 0) {
            return (
                <>
                </>
            )
        }

        return (
            <>
                <h3>Moje slevy</h3>
                <p>Máte kredit <strong>{props.authentication.credits.total} Kč</strong>, který můžete využít jako slevu.</p>
                {renderExpirations()}
                <p>&nbsp;</p>
            </>
        );
    };

    let renderVip = () => {
        let remainingEvents = props.authentication.userProfile.remainingEventsForVip;

        if (remainingEvents === 0) {
            return (
                <>
                    <p>
                        <strong>Děkujeme,</strong> že s námi jezdíte pravidelně a budujete stálý kolektiv kamarádů!
                    </p>
                    <p>
                        <strong>Jste členem našeho věrnostního klubu</strong> a účast na dalších akcích máte
                        za nejnižší možnou cenu.
                    </p>
                </>
            );
        }

        let remainingText = 'ještě ' + remainingEvents + ' akcí.';
        if (remainingEvents === 1) {
            remainingText = 'už jen jedné akce!';
        }

        return (
            <>
                <p>
                    Rodiče dětí, které se k nám vracejí a pomáhají tak budovat stálý kolektiv, odměňujeme členstvím ve věrnostním
                    klubu <strong>s garancí nejnižší ceny</strong>.
                </p>
                <p>
                    Pro získání členství ve věrnostním klubu je potřeba se zúčastnit <strong>{remainingText}</strong>
                </p>
            </>
        );
    };

    return (
        <>
            {renderCredits()}

            <h3>Věrnostní klub</h3>
            <p>
                {renderVip()}
            </p>
        </>
    )
}

export default UserProfileSidebarDiscounts;
