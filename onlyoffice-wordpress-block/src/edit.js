/*
 * (c) Copyright Ascensio System SIA 2023
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

import { MediaPlaceholder, useBlockProps } from '@wordpress/block-editor';
import {onlyofficeIcon} from "./index";
import {blockStyle} from "./index";
const mime = require('mime');

const Edit = ({attributes, setAttributes}) => {
    const onlyofficeAllowedExts = oo_media.formats;
    let onlyofficeAllowedMimes = [];

    for (let ext of onlyofficeAllowedExts) {
        onlyofficeAllowedMimes.push(mime.getType(ext));
    }

    const blockProps = useBlockProps( { style: blockStyle } );
    return (
        attributes.id ?
            <div {...blockProps}>
                <p style={{display: 'flex'}}>
                    {onlyofficeIcon}
                    <p style={{marginLeft: '25px'}}> {attributes.fileName || ""}</p>
                </p>
            </div>
            :
            <MediaPlaceholder
                labels={{title: 'ONLYOFFICE'}}
                allowedTypes={onlyofficeAllowedMimes}
                onSelect={(el) => {
                    setAttributes({ id: el.id, fileName: el.filename || el.title + "." + mime.getExtension(el.mime_type) });
                }}
            />
    );
};

export default Edit;
