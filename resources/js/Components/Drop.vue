<template>
    <div>
         <Button icon="pi pi-times" class="justify-end p-button-rounded p-button-danger"
                                    @click="drop(dropRoute)"
                            />

    </div>
</template>

<script setup>

// import { router } from '@inertiajs/vue3';

import Swal from 'sweetalert2'

const props=defineProps({dropRoute:null})
const drop=()=>Swal.fire({
                                        title: 'Are you sure?',
                                        text: "You won't be able to revert this!",
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonColor: '#3085d6',
                                        cancelButtonColor: '#d33',
                                        confirmButtonText: 'Yes, delete it!'
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                                       axios.delete(props.dropRoute)
                                                                            .then(
                                                                                            Swal.fire(
                                                                                            'Deleted!',
                                                                                            'Item has been deleted.',
                                                                                            'success'
                                                                                    ))
                                                            }
                        })


</script>

<style lang="scss" scoped>

</style>
