import React from 'react'


const InputCheckbox = React.forwardRef((props, ref) => {
    return (
        <div className={'form-checkbox-wrapper' + (props.data.validationError ? ' form-error' : '')}>
            <label className="form-checkbox">
                <span dangerouslySetInnerHTML={{__html: props.label}}/>
                <input ref={ref} onChange={props.onChange} type="checkbox" name={props.name} checked={props.data.value || false} disabled={props.disabled || false}/><span className="form-checkbox-checkmark"/>
            </label>
            <div className="form-error-message">{props.data.validationError}</div>
        </div>
    )
});

export default InputCheckbox
