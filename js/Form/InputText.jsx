import React from 'react'


const InputText = React.forwardRef((props, ref) => {
    let type = 'text';
    let dataInputType = null;
    if (props.type === 'date') {
        dataInputType = 'date';
        if (!props.data.value) {
            type = 'text';
        } else {
            type = 'date';
        }
    }

    let onFocus = (event) => {
        let input = event.target;
        if (dataInputType === 'date') {
            input.type = 'date';
        }

        if (props.onFocus) {
            props.onFocus(event);
        }
    };

    let onBlur = (event) => {
        let input = event.target;
        if (dataInputType === 'date') {
            if (!props.data.value) {
                input.type = 'text';
            } else {
                input.type = 'date';
            }
        }

        if (props.onBlur) {
            props.onBlur(event);
        }
    };

    return (
        <div className={'form-input-group form-input-group-text' + (props.data.value ? ' active' : '') + (props.data.validationError ? ' form-error' : '')}>
            <div className="form-input-group-inner">
                <label htmlFor={props.name}>{props.label}</label>
                <input ref={ref} onBlur={onBlur} onFocus={onFocus} onChange={props.onChange} id={props.name} name={props.name} type={type} defaultValue={props.data.value} data-input-type={dataInputType}/>
            </div>
            <div className="form-error-message">{props.data.validationError}</div>
        </div>
    )
});

export default InputText
