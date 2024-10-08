<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref,computed,onMounted } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Toolbar from 'primevue/toolbar';
import PieChart from '@/Components/PieChart.vue';
import Swal from 'sweetalert2';
import { swal } from 'sweetalert2/dist/sweetalert2.all';

const props=defineProps({meeting:Object,members:Object,guests:Object,meeting_lines:Object});
const membersArray=ref([...props.members]);
const guestsArray=ref([...props.guests]);
const membersInMeeting = props.meeting_lines.filter(line => line.user_type === 'member');

const totalMembers = membersArray.value.length;
const attendedMembers = membersInMeeting.filter(line => line.score > 0).length;
const absentMembers = totalMembers - attendedMembers;

const membersAttendanceChartData = ref({
  labels: ['Attended', 'Absent'],
  datasets: [
    {
      label: 'Members Attendance',
      data: [attendedMembers, absentMembers],
      backgroundColor: ['#36A2EB', '#FF6384'],
    },
  ],
});

const guestsVsMembersChartData = ref({
  labels: ['Members', 'Guests'],
  datasets: [
    {
      label: 'Guests vs Members',
      data: [membersArray.value.length, guestsArray.value.length],
      backgroundColor: ['#4BC0C0', '#FFCE56'],
    },
  ],
});
const handleAttendanceChange = (data, userType) => {
    // If not attended, mark them as attended and trigger SweetAlert
    if (!data.attended) {
        data.attended = true;
        data.score = 1; // Automatically update the score
        updateAttendance(data.id, data.attended, userType);
        updateScore(data.id, data.score, userType);

        // Show success alert and clear search input after confirming
        Swal.fire({
            icon: 'success',
            title: 'Attendance marked!',
            text: `${data.name} has been marked as attended.`,
        }).then(() => {
            clearSearchInput(); // Clear search input
            focusSearchInput(); // Refocus on search input
        });
    }
};

const clearSearchInput = () => {
    searchMember = '';  // Clear member search input
    searchGuest = '';   // Clear guest search input
};

const updateAttendance = (id, attended, userType) => {
    const score = attended ? '1' : '0'; // Score is sent as a string

    axios.post(route('meetings.attend'), {
            userId: id,
            userType,
            score,
            attendedFrom: '18:00', // Replace with actual value if applicable
            attendedTo: '20:00',   // Replace with actual value if applicable
            meeting_id: props.meeting.id // Replace with actual meeting ID
        })
        .then(response => {
            console.log('Attendance updated successfully', response.data);
        })
        .catch(error => {
            console.error('Error updating attendance', error);
        });
};



const focusSearchInput = () => {
    // Automatically focus the search input after attendance is updated
    const searchInput = document.querySelector('input[placeholder="Search"]');
    if (searchInput) {
        searchInput.focus();
    }
};

const updateScore = (id, score, userType, attendedFrom, attendedTo) => {
    axios.post(route('meetings.attend'), {
        userId: id,
        userType,
        score,
        attendedFrom,
        attendedTo,
        meeting_id: props.meeting.id
    })
    .then(response => {
        console.log('Score updated successfully', response.data);
        focusSearchInput()
    })
    .catch(error => {
        console.error('Error updating score', error);
    });
};
const searchMember=ref('')
const searchGuest=ref('')
const filterUsers = (searchTerm, userArray, userType) => {
    const lowercasedSearchTerm = searchTerm.toLowerCase();

    // Find the first matching user who is not already marked as attended
    const matchedUser = userArray.value.find(user =>
                (user.name.toLowerCase().includes(lowercasedSearchTerm) ||
                 user.email.toLowerCase().includes(lowercasedSearchTerm) ||
                 user.phone.toLowerCase().includes(lowercasedSearchTerm))
                //  &&
                // !user.attended
            );

            // If a matching user is found, mark them as attended
            if (matchedUser && searchTerm && !matchedUser.attended)
                {
                    matchedUser.attended = true;
                    matchedUser.score = 1; // Automatically set score to 1


                    updateAttendance(matchedUser.id, matchedUser.attended, userType);
                    updateScore(matchedUser.id, matchedUser.score, userType);
                    showSuccessAlert(matchedUser.name);
                }
               else
                 {
                    // Swal.fire('Already Marked','User already marked present','info')
                    // clearSearchInput();
                    // focusSearchInput();
                }
                 // Show SweetAlert


            // Return the filtered users for display
            return userArray.value.filter(user =>
                user.name.toLowerCase().includes(lowercasedSearchTerm) ||
                user.email.toLowerCase().includes(lowercasedSearchTerm) ||
                user.phone.toLowerCase().includes(lowercasedSearchTerm)
            );
        };


const filteredMembers = computed(() => filterUsers(searchMember.value, membersArray, 'member'));
const filteredGuests = computed(() => filterUsers(searchGuest.value, guestsArray, 'guest'));

const showSuccessAlert = (userName) => {
    Swal.fire({
        title: 'Success!',
        text: `${userName} marked as attended.`,
        icon: 'success',
        timer: 2000
    }).then(() => {
        clearSearch();
    });
};

const clearSearch = () => {
    searchMember.value = '';
    searchGuest.value = '';
};

const initializeUserData = (userArray, userType) => {
    props.meeting_lines.forEach(line => {
        if (line.user_type === userType) {
            const user = userArray.value.find(m => m.id === line.user_id);
            if (user) {
                user.attended = line.score === '1'; // Set checkbox status
                user.attendedFrom = line.attended_from;
                user.attendedTo = line.attended_to;
                user.score = line.score;
                user.meetingId = line.meeting_id;
            }
        }
    });
};


const value = ref('0'); // Set default tab value to '0'

onMounted(() => {
    initializeUserData(membersArray,'member');
    initializeUserData(guestsArray,'guest');
    focusSearchInput();

});

</script>


<template>
    <Head :title="`Meeting ${meeting.meeting_no}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Meeting {{ meeting.meeting_no }} </h2>
        </template>
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">

            <div class="flex justify-end gap-2 mb-2">
                <Button @click="value = '0'" rounded label="1" class="w-8 h-8 p-0" :outlined="value !== '0'" />
                <Button @click="value = '1'" rounded label="2" class="w-8 h-8 p-0" :outlined="value !== '1'" />
                <Button @click="value = '2'" rounded label="3" class="w-8 h-8 p-0" :outlined="value !== '2'" />
            </div>

            <Tabs v-model:value="value">
                <TabList>
                    <Tab value="0">Members</Tab>
                    <Tab value="1">Guests</Tab>
                    <Tab value="2">Stats</Tab>
                </TabList>
                <TabPanels>
                    <TabPanel value="0">
                        <Toolbar>
                            <template #center>

                                 <InputText placeholder="Search" v-model="searchMember"/>

                            </template>
                        </Toolbar>
                         <DataTable :value="filteredMembers" dataKey="id">
                                <Column field="name" header="Member Name"></Column>
                                <Column field="phone" header="Phone Number"></Column>
                                <Column field="email" header="Email"></Column>
                                  <Column header="Attended?">
                                    <template #body="{ data }">
                                        <input type="checkbox"
                                            v-model="data.attended"
                                            @change="handleAttendanceChange(data,'member')" />
                                    </template>
                                </Column>
                                <Column header="Score">
                                    <template #body="{ data }">
                                        <InputText size="small"
                                                style="width: 40px;"
                                                v-model="data.score"
                                                @input="updateScore(data.id, data.score, 'member')" />
                                    </template>
                                </Column>

                                 <!-- From Column -->
                                <Column header="From">
                                    <template #body="{ data }">
                                        <InputText
                                            v-model="data.attendedFrom"
                                            :placeholder="'18:00'"
                                            size="small"
                                            @input="updateAttendance(data.id, data.attended, data.user_type, data.attendedFrom, data.attendedTo)"
                                        />
                                    </template>
                                </Column>
                                <!-- To Column -->
                                <Column header="To">
                                    <template #body="{ data }">
                                        <InputText
                                            v-model="data.attendedTo"
                                            :placeholder="'20:00'"
                                            size="small"
                                            @input="updateAttendance(data.id, data.attended, data.userType, data.attendedFrom, data.attendedTo)"
                                        />
                                    </template>
                                </Column>
                            </DataTable>
                    </TabPanel>
                    <TabPanel value="1">
                          <Toolbar>
                            <template #center>

                                 <InputText placeholder="Search" v-model="searchGuest"/>

                            </template>
                        </Toolbar>
                         <DataTable :value="filteredGuests" dataKey="id">
                                <Column field="name" header="Guest Name"></Column>
                                <Column field="phone" header="Phone Number"></Column>
                                <Column field="email" header="Email"></Column>
                                <Column header="Attended?">
                                <template #body="{ data }">
                                    <input type="checkbox"
                                        v-model="data.attended"
                                        @change="handleAttendanceChange(data,'guest')" />
                                </template>
                            </Column>
                            <Column header="Score">
                                <template #body="{ data }">
                                    <InputText size="small"
                                            style="width: 40px;"
                                            v-model="data.score"
                                            @input="updateScore(data.id, data.score, 'guest')" />
                                </template>
                            </Column>

                                 <!-- From Column -->
                                <Column header="From">
                                    <template #body="{ data }">
                                        <InputText
                                            v-model="data.attendedFrom"
                                            :placeholder="'18:00:00'"
                                            size="small"
                                            @input="updateAttendance(data.id, data.attended, data.user_type, data.attendedFrom, data.attendedTo)"
                                        />
                                    </template>
                                </Column>
                                <!-- To Column -->
                                <Column header="To">
                                    <template #body="{ data }">
                                        <InputText
                                            v-model="data.attendedTo"
                                            :placeholder="'20:00:00'"
                                            size="small"
                                            @input="updateAttendance(data.id, data.attended, data.userType, data.attendedFrom, data.attendedTo)"
                                        />
                                    </template>
                                </Column>
                            </DataTable>
                    </TabPanel>
                    <TabPanel value="2">
                        <p class="m-0">
                            <div class="flex flex-row justify-start gap-4 p-2 m-1">
                                <!-- <div >
                                    <h2>Members Pie Chart</h2>
                                   <PieChart :data="membersChartData" />
                                </div>

                                <div >

                                    <h2>Guests Pie Chart</h2>
                                     <PieChart :data="guestsChartData"  />
                                </div> -->
                                <div>
                                    <h2>Guests vs Members Pie Chart</h2>
                                    <PieChart :data="guestsVsMembersChartData" />
                                </div>

                                <div>
                                    <h2>Members Attendance Pie Chart</h2>
                                      <PieChart :data="membersAttendanceChartData" />
                                </div>

                            </div>
                       </p>
                    </TabPanel>
                </TabPanels>
            </Tabs>

            </div>
        </div>
    </AuthenticatedLayout>


</template>



