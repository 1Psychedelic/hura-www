import React from 'react'


const Crash = (props) => {
    return (
        <>
            <section className="page__section page__section--generic-heading">
                <div className="page__section__content">
                    <span className="page__section__subheading"></span>
                    <h2 className="page__section__heading"></h2>
                </div>
            </section>
            <section className="page__section page__section--reservation-detail">
                <div className="page__section__content">

                    <a onClick={thisFunctionDoesNotExist}>crash me!</a>

                </div>
            </section>
        </>

    )
};

export default Crash
