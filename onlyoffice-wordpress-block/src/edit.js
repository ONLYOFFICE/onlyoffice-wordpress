/*
 * (c) Copyright Ascensio System SIA 2022
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

import {
    MediaPlaceholder,
    useBlockProps,
    InspectorControls
} from '@wordpress/block-editor';
import {
    PanelBody,
    RadioControl,
    Button,
    SelectControl
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import {onlyofficeIcon} from "./index";
import {blockStyle} from "./index";
const mime = require('mime');

const Edit = ({attributes, setAttributes, clientId}) => {
    const [url, setUrl] = useState(attributes.url);
    const [ruleIndex, setRuleIndex] = useState(0);
    const [currentExt, setCurrentExt] = useState(null);
    const selectorRules = ['Full access', 'Read only', 'Review', 'Comment'].map((ooRule, key) => {
        return {label: __(ooRule, 'onlyoffice-plugin'), value: ooRule.toLowerCase()}
    });

    const onlyofficeAllowedExts = attributes.formats || oo_media.formats;
    let onlyofficeAllowedMimes = [];

    for (let ext of onlyofficeAllowedExts) {
        onlyofficeAllowedMimes.push(mime.getType(ext));
    }

    if (!url && attributes.selectedAttachment && attributes.selectedAttachment.id) {
        const editorUrl = attributes.getEditorUrl + attributes.selectedAttachment.id;

        fetch(editorUrl).then((r) => r.json()).then((data) => {
            setUrl(data.url);
            setAttributes({ url: data.url });
        });
    }

    useEffect(() => {
        // componentWillUnmount
        return () => {
            // Your code here
        }
    }, [yourDependency]);

    useEffect(() => {
        const attachUrl = attributes.selectedAttachment.url;
        setCurrentExt(attachUrl.substring(attachUrl.lastIndexOf('.') + 1));
    }, [attributes.selectedAttachment]);

    const addOrDeleteRule = (type, key = null) => {
        if (type === 'add') {
            setAttributes({specificRules: [...attributes.specificRules, {role: Object.keys(oo_media.roles[ruleIndex]), permission: 'read only'}]});
            setRuleIndex(ruleIndex + 1);
        } else {
            setRuleIndex(ruleIndex - 1);
        }
    }

    const changeSpecificPermission = (value, key) => {

    }

    const changeSpecificRole = (value, key) => {

    }

    let intervalCheckPostIsSaved;
    let ajaxRequest;

    if (attributes.url) {
        const unsubscribe = wp.data.subscribe(function () {
            let wpCoreEditor = wp.data.select('core/editor');
            if (wpCoreEditor.isSavingPost() && !wpCoreEditor.isAutosavingPost() && wpCoreEditor.didPostSaveRequestSucceed()) {
                if (!intervalCheckPostIsSaved) {
                    intervalCheckPostIsSaved = setInterval(function () {
                        if (!wp.data.select('core/editor').isSavingPost()) {
                            if (ajaxRequest) {
                                ajaxRequest.abort();
                            }
                            const getBlockList = () => wp.data.select( 'core/block-editor' ).getBlocks();
                            let blockDeleted = true;
                            for (let block of getBlockList()) {
                                if (block.clientId === clientId) {
                                    blockDeleted = false;
                                }
                            }

                            const data = {
                                type: !blockDeleted ? 'save' : 'delete',
                                postId: wpCoreEditor.getCurrentPostId(),
                                blockId: clientId,
                                rules: attributes.rule === 'common' ? attributes.commonRule : attributes.specificRules
                            }
                            console.log(data);
                            // ajaxRequest = $.ajax({
                            //     url: oo_media.saveShareSettingsUrl,
                            //     type: 'POST',
                            //     dataType: 'json',
                            //     data: data,
                            //     success: function (response) {
                            //         ajaxRequest = null;
                            //     }
                            // });

                            clearInterval(intervalCheckPostIsSaved);
                            intervalCheckPostIsSaved = null;
                            unsubscribe();
                        }
                    }, 800);
                }
            }
        });
    }

    const blockProps = useBlockProps( { style: blockStyle } );
    return (
        attributes.selectedAttachment && attributes.selectedAttachment.id ?
            <div {...blockProps}>
                <InspectorControls key="setting">
                    <PanelBody title={__('Share Settings', 'onlyoffice-plugin')}>
                        <RadioControl
                            selected={ attributes.rule }
                            options={ [
                                { label: __('Use common rules:', 'onlyoffice-plugin'), value: 'common' },
                                { label: __('Define more specific rules:', 'onlyoffice-plugin'), value: 'specific' },
                            ] }
                            onChange={ ( value ) => setAttributes({rule: value}) }
                        />
                        {attributes.specificRules && attributes.specificRules.map((rule, key) => {
                            return (
                                <div style={{display: 'flex', flexDirection: 'row'}}>
                                    <SelectControl
                                        onChange={(value) => changeSpecificRole(value, key)}
                                        options={oo_media.roles.length === attributes.specificRules.length ? [] : }
                                    />
                                    <SelectControl
                                        onChange={(value) => changeSpecificPermission(value, key)}
                                        options={currentExt === 'oform' ? [...selectorRules, {label: __('Form filling', 'onlyoffice-plugin'), value: 'form filling'}] : selectorRules}
                                    />
                                    <Button onClick={() => addOrDeleteRule('delete', key)}/>
                                </div>
                            )
                        })}
                        <Button onClick={() => addOrDeleteRule('add')} variant={'link'} text={__('Add rule', 'onlyoffice-plugin')}
                                disabled={attributes.rule === 'common' || oo_media.roles.length === attributes.specificRules.length}/>
                    </PanelBody>
                </InspectorControls>
                <p style={{display: 'flex'}}>
                    {onlyofficeIcon}
                    <p style={{marginLeft: '25px'}}> {attributes.selectedAttachment.filename || `${attributes.selectedAttachment.title}.${currentExt}`}</p>
                </p>
            </div>
            :
            <MediaPlaceholder
                labels={{title: 'ONLYOFFICE'}}
                allowedTypes={onlyofficeAllowedMimes}
                onSelect={(el) => {
                    setAttributes({selectedAttachment: el, getEditorUrl: oo_media.getEditorUrl, formats: oo_media.formats});
                }}
            />
    );
};

export default Edit;
