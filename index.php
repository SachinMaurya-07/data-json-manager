<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">

<title>DATA.JSON Manager</title>

<style>

*{
    box-sizing:border-box;
}

body{
    margin:0;
    font-family:Arial,Helvetica,sans-serif;
    background:#f4f6fa;
    color:#111827;
}

.header{
    background:#111827;
    color:white;
    padding:25px;
}

.header h1{
    margin:0;
    font-size:27px;
}

.header p{
    color:#9ca3af;
    margin:7px 0 0;
}

.container{
    max-width:1250px;
    margin:28px auto;
    padding:0 18px;
}

.toolbar{
    display:flex;
    gap:10px;
    margin-bottom:20px;
}

.search{
    flex:1;
    border:1px solid #d1d5db;
    border-radius:9px;
    padding:14px;
    font-size:15px;
}

button{
    border:0;
    border-radius:8px;
    padding:11px 15px;
    cursor:pointer;
    font-weight:600;
}

.add{
    background:#16a34a;
    color:white;
}

.refresh{
    background:#111827;
    color:white;
}

.card{
    background:white;
    border:1px solid #e5e7eb;
    border-radius:14px;
    margin-bottom:20px;
    overflow:hidden;
}

.card-head{
    padding:18px 20px;
    border-bottom:1px solid #e5e7eb;
    display:flex;
    justify-content:space-between;
    gap:15px;
}

.json-url{
    font-family:monospace;
    word-break:break-all;
}

.badge{
    white-space:nowrap;
    border-radius:20px;
    padding:5px 10px;
    font-size:12px;
}

.ok{
    background:#dcfce7;
    color:#166534;
}

.bad{
    background:#fee2e2;
    color:#991b1b;
}

.body{
    padding:20px;
}

.item{
    padding:15px 0;
    border-bottom:1px solid #eee;
}

.item:last-child{
    border-bottom:0;
}

.name{
    font-size:17px;
    font-weight:bold;
    margin-bottom:9px;
}

.url{
    background:#f3f4f6;
    border-radius:8px;
    padding:12px;
    font-family:monospace;
    word-break:break-all;
}

.url a{
    color:#2563eb;
}

.description{
    color:#6b7280;
    margin-top:8px;
}

.actions{
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    margin-top:14px;
}

.edit{
    background:#2563eb;
    color:white;
}

.history-btn{
    background:#f3f4f6;
}

.raw{
    background:#111827;
    color:white;
}

.delete{
    background:#fee2e2;
    color:#991b1b;
}

.history-box{
    display:none;
    margin-top:15px;
    background:#fafafa;
    border:1px solid #e5e7eb;
    padding:15px;
    border-radius:9px;
}

.history-row{
    padding:10px 0;
    border-bottom:1px solid #eee;
}

.history-row:last-child{
    border-bottom:0;
}

.warning{
    display:none;
    background:#fff7ed;
    border:1px solid #fed7aa;
    color:#9a3412;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
}

.modal{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.55);
    display:none;
    align-items:center;
    justify-content:center;
    padding:20px;
    z-index:100;
}

.modal-box{
    width:100%;
    max-width:600px;
    background:white;
    border-radius:14px;
    padding:25px;
}

.modal-box h2{
    margin-top:0;
}

.input{
    width:100%;
    padding:13px;
    border:1px solid #d1d5db;
    border-radius:8px;
    margin:8px 0 15px;
}

.save{
    background:#16a34a;
    color:white;
}

.cancel{
    background:#e5e7eb;
}

.empty{
    color:#9ca3af;
}

pre{
    background:#111827;
    color:#e5e7eb;
    padding:15px;
    border-radius:8px;
    overflow:auto;
}

@media(max-width:700px){

    .toolbar{
        flex-direction:column;
    }

    .card-head{
        flex-direction:column;
    }

}

</style>
</head>

<body>

<div class="header">

    <h1>DATA.JSON Manager</h1>

    <p>
        Manage JSON files, destination links and URL history
    </p>

</div>


<div class="container">

    <div class="toolbar">

        <input
            class="search"
            id="search"
            placeholder="Search JSON, name, destination URL..."
            oninput="filterCards()"
        >

        <button
            class="add"
            onclick="openAdd()"
        >
            + Add data.json
        </button>

        <button
            class="refresh"
            onclick="loadData()"
        >
            ↻ Refresh
        </button>

    </div>


    <div id="app">
        Loading...
    </div>

</div>


<!-- EDIT MODAL -->

<div
    class="modal"
    id="editModal"
>

    <div class="modal-box">

        <h2>Change Destination URL</h2>

        <div id="editInfo"></div>

        <div
            class="warning"
            id="editWarning"
        ></div>

        <input
            class="input"
            id="newUrl"
            placeholder="https://example.com/new-link"
        >

        <button
            class="save"
            onclick="saveUrl()"
        >
            Save
        </button>

        <button
            class="cancel"
            onclick="closeModal('editModal')"
        >
            Cancel
        </button>

    </div>

</div>


<!-- ADD MODAL -->

<div
    class="modal"
    id="addModal"
>

    <div class="modal-box">

        <h2>Add data.json</h2>

        <p>
            Enter the public URL and the local path on your Hostinger account.
        </p>

        <label>Public URL</label>

        <input
            class="input"
            id="addUrl"
            placeholder="https://flyhighmarketing.in/new/data.json"
        >

        <label>Local file path</label>

        <input
            class="input"
            id="addFile"
            placeholder="/new/data.json"
        >

        <p style="font-size:13px;color:#6b7280">

            Example:
            <br>
            Public URL:
            https://flyhighmarketing.in/follow2/data.json
            <br><br>
            Local path:
            /follow2/data.json

        </p>

        <button
            class="save"
            onclick="addJson()"
        >
            Add JSON
        </button>

        <button
            class="cancel"
            onclick="closeModal('addModal')"
        >
            Cancel
        </button>

    </div>

</div>


<script>

let allData = [];

let editState = null;


/* LOAD */

async function loadData(){

    document.getElementById('app').innerHTML =
        '<p>Loading...</p>';

    try{

        const response =
            await fetch('api.php?action=list');

        const result =
            await response.json();

        if(!result.success){

            throw new Error(result.error);

        }

        allData =
            result.files;

        render();

    }catch(error){

        document.getElementById('app').innerHTML =
            `<p class="empty">
                ${escapeHtml(error.message)}
            </p>`;

    }

}


/* RENDER */

function render(){

    let html = '';

    if(!allData.length){

        document.getElementById('app').innerHTML =
            '<p class="empty">No JSON files registered.</p>';

        return;

    }


    allData.forEach((file,fileIndex)=>{

        html += `

        <div
            class="card searchable"
        >

            <div class="card-head">

                <div class="json-url">

                    ${escapeHtml(file.url)}

                </div>

                <span
                    class="badge ${
                        file.status === 'ok'
                        ? 'ok'
                        : 'bad'
                    }"
                >

                    ${
                        file.status === 'ok'
                        ? 'JSON OK'
                        : 'ERROR'
                    }

                </span>

            </div>


            <div class="body">

        `;


        if(
            file.status === 'ok' &&
            file.items.length
        ){

            file.items.forEach(
                (item,itemIndex)=>{

                    html += `

                    <div class="item">

                        <div class="name">

                            ${escapeHtml(
                                item.name ||
                                'Unnamed'
                            )}

                        </div>


                        <div class="url">

                            <a
                                href="${escapeHtml(
                                    item.url || '#'
                                )}"
                                target="_blank"
                            >

                                ${escapeHtml(
                                    item.url || ''
                                )}

                            </a>

                        </div>


                        <div class="description">

                            ${escapeHtml(
                                item.des || ''
                            )}

                        </div>


                        <div class="actions">

                            <button
                                class="edit"
                                onclick="openEdit(
                                    ${fileIndex},
                                    ${itemIndex}
                                )"
                            >
                                ✏️ Edit URL
                            </button>


                            <button
                                class="history-btn"
                                onclick="toggleHistory(
                                    ${fileIndex},
                                    ${itemIndex}
                                )"
                            >
                                🕘 History
                            </button>


                            <button
                                class="raw"
                                onclick="showRaw(
                                    ${fileIndex}
                                )"
                            >
                                View JSON
                            </button>

                        </div>


                        <div
                            class="history-box"
                            id="history-${fileIndex}-${itemIndex}"
                        >

                            ${renderHistory(
                                item.history
                            )}

                        </div>

                    </div>

                    `;

                }
            );

        }else{

            html += `
                <div class="empty">
                    Unable to load JSON.
                </div>
            `;

        }


        html += `

            <div class="actions">

                <button
                    class="delete"
                    onclick="removeJson(${fileIndex})"
                >
                    🗑️ Remove from Manager
                </button>

            </div>

            </div>

        </div>

        `;

    });


    document.getElementById('app').innerHTML =
        html;

}


/* HISTORY */

function renderHistory(history){

    if(
        !history ||
        !history.length
    ){

        return `
            <div class="empty">
                No previous URLs recorded.
            </div>
        `;

    }


    return history
        .slice()
        .reverse()
        .map(entry=>`

            <div class="history-row">

                🟡 <strong>Previously used</strong>

                <div
                    style="
                    word-break:break-all;
                    margin-top:5px;
                    "
                >
                    ${escapeHtml(entry.url)}
                </div>

                <small>
                    ${escapeHtml(entry.date)}
                </small>

            </div>

        `)
        .join('');

}


/* EDIT */

function openEdit(fileIndex,itemIndex){

    editState = {
        fileIndex,
        itemIndex
    };


    const file =
        allData[fileIndex];

    const item =
        file.items[itemIndex];


    document.getElementById('editInfo')
        .innerHTML = `

            <p>
                <strong>
                    ${escapeHtml(
                        item.name || 'Unnamed'
                    )}
                </strong>
            </p>

            <small>
                ${escapeHtml(file.url)}
            </small>

        `;


    document.getElementById('newUrl').value =
        item.url || '';

    document.getElementById('editWarning')
        .style.display = 'none';


    document.getElementById('editModal')
        .style.display = 'flex';

}


/* SAVE URL */

async function saveUrl(){

    if(!editState){
        return;
    }


    const newUrl =
        document.getElementById('newUrl')
        .value
        .trim();


    if(!newUrl){

        alert('Enter a URL.');

        return;

    }


    if(
        !/^https?:\/\//i.test(newUrl)
    ){

        alert(
            'URL must start with http:// or https://'
        );

        return;

    }


    try{

        const check =
            await fetch(
                'api.php?action=check_url&url=' +
                encodeURIComponent(newUrl)
            );


        const result =
            await check.json();


        if(result.previouslyUsed){

            let message =
                '⚠️ This URL was previously used.';

            if(result.currentLocation){

                message +=
                    '<br>Currently used by:<br>' +
                    escapeHtml(
                        result.currentLocation
                    );

            }

            document.getElementById(
                'editWarning'
            ).innerHTML = message;


            document.getElementById(
                'editWarning'
            ).style.display = 'block';


            if(
                !confirm(
                    'This URL was previously used. Continue?'
                )
            ){

                return;

            }

        }


        const file =
            allData[
                editState.fileIndex
            ];


        const response =
            await fetch(
                'api.php?action=update',
                {
                    method:'POST',

                    headers:{
                        'Content-Type':
                            'application/json'
                    },

                    body:JSON.stringify({

                        jsonUrl:file.url,

                        itemIndex:
                            editState.itemIndex,

                        newUrl:newUrl

                    })

                }
            );


        const save =
            await response.json();


        if(!save.success){

            throw new Error(
                save.error ||
                'Update failed.'
            );

        }


        alert(
            'URL updated successfully.'
        );


        closeModal('editModal');

        loadData();


    }catch(error){

        alert(error.message);

    }

}


/* ADD JSON */

async function addJson(){

    const url =
        document.getElementById('addUrl')
        .value
        .trim();

    const file =
        document.getElementById('addFile')
        .value
        .trim();


    if(!url || !file){

        alert(
            'Enter both public URL and local file path.'
        );

        return;

    }


    if(
        !/^https?:\/\//i.test(url)
    ){

        alert(
            'Public URL must start with http:// or https://'
        );

        return;

    }


    try{

        const response =
            await fetch(
                'api.php?action=add',
                {
                    method:'POST',

                    headers:{
                        'Content-Type':
                            'application/json'
                    },

                    body:JSON.stringify({

                        url:url,

                        file:file

                    })

                }
            );


        const result =
            await response.json();


        if(!result.success){

            throw new Error(
                result.error
            );

        }


        alert(
            'data.json added successfully.'
        );


        document.getElementById('addUrl')
            .value = '';

        document.getElementById('addFile')
            .value = '';


        closeModal('addModal');

        loadData();


    }catch(error){

        alert(error.message);

    }

}


/* REMOVE */

async function removeJson(index){

    const file =
        allData[index];


    if(
        !confirm(
            'Remove this JSON from the manager?\\n\\nThe actual data.json file will NOT be deleted.'
        )
    ){

        return;

    }


    try{

        const response =
            await fetch(
                'api.php?action=remove',
                {
                    method:'POST',

                    headers:{
                        'Content-Type':
                            'application/json'
                    },

                    body:JSON.stringify({
                        url:file.url
                    })

                }
            );


        const result =
            await response.json();


        if(!result.success){

            throw new Error(
                result.error
            );

        }


        loadData();


    }catch(error){

        alert(error.message);

    }

}


/* HISTORY TOGGLE */

function toggleHistory(
    fileIndex,
    itemIndex
){

    const element =
        document.getElementById(
            `history-${fileIndex}-${itemIndex}`
        );


    element.style.display =
        element.style.display === 'block'
            ? 'none'
            : 'block';

}


/* RAW JSON */

function showRaw(index){

    const json =
        JSON.stringify(
            allData[index].raw,
            null,
            2
        );


    const windowRef =
        window.open('','_blank');


    windowRef.document.write(
        '<pre>' +
        escapeHtml(json) +
        '</pre>'
    );

}


/* ADD MODAL */

function openAdd(){

    document.getElementById('addModal')
        .style.display = 'flex';

}


/* CLOSE */

function closeModal(id){

    document.getElementById(id)
        .style.display = 'none';

}


/* SEARCH */

function filterCards(){

    const query =
        document.getElementById('search')
        .value
        .toLowerCase();


    document
        .querySelectorAll('.searchable')
        .forEach(card=>{

            card.style.display =
                card.innerText
                    .toLowerCase()
                    .includes(query)
                    ? ''
                    : 'none';

        });

}


/* ESCAPE */

function escapeHtml(value){

    return String(value ?? '')
        .replaceAll('&','&amp;')
        .replaceAll('<','&lt;')
        .replaceAll('>','&gt;')
        .replaceAll('"','&quot;')
        .replaceAll("'","&#039;");

}


loadData();

</script>

</body>
</html>