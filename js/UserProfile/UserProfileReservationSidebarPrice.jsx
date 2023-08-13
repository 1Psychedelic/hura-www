import React from 'react'


const UserProfileReservationSidebarPrice = (props) => {
    let reservationChildren = props.children;

    let totalPrice = 0;
    let groups = [];

    if (reservationChildren.length > 0) {
        let subtotal = reservationChildren.length * props.event.price;
        totalPrice += subtotal;
        let childrenGroup = {
            'title': props.event.name,
            'items': [],
            'subtotal': subtotal
        };
        for (let i in reservationChildren) {
            childrenGroup.items.push({
                'title': reservationChildren[i].name,
                'price': props.event.price
            });
        }
        groups.push(childrenGroup);
    }

    if (props.addons) {
        let addonsGroup = {
            'title': 'Doplňkové služby',
            'items': [],
            'subtotal': 0
        };
        for (let i in props.addons) {
            if (props.addons[i].amount > 0) {
                let addonPrice = props.addons[i].price * props.addons[i].amount;
                addonsGroup.items.push({
                    'title': props.addons[i].amount + '× ' + props.addons[i].name,
                    'price': addonPrice,
                });
                addonsGroup.subtotal += addonPrice;
                totalPrice += addonPrice;
            }
        }
        if (addonsGroup.items.length > 0) {
            groups.push(addonsGroup);
        }
    }

    if (props.reservation.discounts.payingByDiscountCode > 0 || props.reservation.discounts.payingByCredit > 0) {
        let discountsGroup = {
            'title': 'Slevy',
            'items': [],
            'subtotal': 0
        };

        if (props.reservation.discounts.payingByDiscountCode > 0) {
            discountsGroup.items.push({
                'title': 'Slevový kód "' + props.reservation.discounts.discountCode + '"',
                'price': -props.reservation.discounts.payingByDiscountCode
            });
            discountsGroup.subtotal -= props.reservation.discounts.payingByDiscountCode;
        }

        if (props.reservation.discounts.payingByCredit > 0) {
            discountsGroup.items.push({
                'title': 'Platba kreditem',
                'price': -props.reservation.discounts.payingByCredit
            });
            discountsGroup.subtotal -= props.reservation.discounts.payingByCredit;
        }

        groups.push(discountsGroup);
    }

    let createPriceGroupItem = (groupItem) => {
        return (
            <div key={groupItem.title} className="reservation-sidebar-price-group-item">
                <div className="reservation-sidebar-price-group-item-title">
                    {groupItem.title}
                </div>
                <div className="reservation-sidebar-price-group-item-price">
                    {groupItem.price.toLocaleString('cs-CZ') + " Kč"}
                </div>
            </div>
        );
    };

    let createPriceGroupItems = (groupItems) => {
        return groupItems.map(createPriceGroupItem);
    };

    let createPriceGroup = (group) => {
        return (
            <div key={group.title} className="reservation-sidebar-price-group">
                <h4>{group.title}</h4>
                {createPriceGroupItems(group.items)}
                <div className="reservation-sidebar-price-group-subtotal">
                    {group.subtotal.toLocaleString('cs-CZ') + " Kč"}
                </div>
            </div>
        );
    };

    let createPriceGroups = (groups) => {
        return groups.map(createPriceGroup);
    };

    return (
        <div className="reservation-form-sidebar">
            <h3>Cena celkem</h3>
            <div className="reservation-form-sidebar-subheading">
                <span>včetně DPH</span>
            </div>

            <div className="reservation-form-sidebar-price">
                {createPriceGroups(groups)}
                <div className="reservation-form-sidebar-price-total">
                    Celkem: <span>{props.reservation.price.toLocaleString('cs-CZ') + " Kč"}</span>
                </div>
            </div>
        </div>
    )
};

export default UserProfileReservationSidebarPrice
